<?php

class Oxygen_EventListener_LoginListener
{
    /**
     * @var Oxygen_Security_Nonce_NonceManagerInterface
     */
    private $nonceManager;

    /**
     * @var Oxygen_Drupal_UserManager
     */
    private $userManager;

    /**
     * @var Oxygen_Drupal_SessionManager
     */
    private $sessionManager;

    /**
     * @var Oxygen_Security_Rsa_RsaVerifierInterface
     */
    private $rsaVerifier;

    /**
     * @var Oxygen_Drupal_StateInterface
     */
    private $state;

    /**
     * @var Oxygen_Drupal_Context
     */
    private $context;

    /**
     * @param Oxygen_Security_Nonce_NonceManagerInterface $nonceManager
     * @param Oxygen_Drupal_UserManager                   $userManager
     * @param Oxygen_Drupal_SessionManager                $sessionManager
     * @param Oxygen_Security_Rsa_RsaVerifierInterface    $rsaVerifier
     * @param Oxygen_Drupal_StateInterface                $state
     * @param Oxygen_Drupal_Context                       $context
     */
    public function __construct(Oxygen_Security_Nonce_NonceManagerInterface $nonceManager, Oxygen_Drupal_UserManager $userManager, Oxygen_Drupal_SessionManager $sessionManager, Oxygen_Security_Rsa_RsaVerifierInterface $rsaVerifier, Oxygen_Drupal_StateInterface $state, Oxygen_Drupal_Context $context)
    {
        $this->nonceManager   = $nonceManager;
        $this->userManager    = $userManager;
        $this->sessionManager = $sessionManager;
        $this->rsaVerifier    = $rsaVerifier;
        $this->state          = $state;
        $this->context        = $context;
    }

    public function onPublicRequest(Oxygen_Event_PublicRequestEvent $event)
    {
        $params = $event->getRequest()->query;

        // @TODO: Move the logic below to kernel? We need at least one more action for that, or else it's an overkill.
        if (!isset($params['oxygenRequestId'])) {
            return;
        }

        if (!is_string($params['oxygenRequestId'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_REQUEST_ID_NOT_VALID);
        }

        if (!isset($params['actionName'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_NAME_NOT_PROVIDED);
        }

        if (!is_string($params['actionName'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_ACTION_NAME_NOT_VALID);
        }

        if ($params['actionName'] !== 'site.login') {
            return;
        }

        if (!isset($params['requestExpiresAt'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_EXPIRATION_NOT_PROVIDED);
        }

        // The "is_numeric" check with more predictable behavior.
        if ((string)(int)$params['requestExpiresAt'] !== (string)$params['requestExpiresAt']) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_EXPIRATION_NOT_VALID);
        }

        if (!isset($params['signature'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_SIGNATURE_NOT_PROVIDED);
        }

        if (!is_string($params['signature'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_SIGNATURE_NOT_VALID);
        }

        if (!isset($params['username'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USERNAME_NOT_PROVIDED);
        }

        if (!is_string($params['username'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USERNAME_NOT_VALID);
        }

        if (!isset($params['userUid'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USER_UID_NOT_PROVIDED);
        }

        if (!is_string($params['userUid']) || !preg_match('{^U\d{10}$}', $params['userUid'])) {
            throw new Oxygen_Exception(Oxygen_Exception::PROTOCOL_USER_UID_NOT_VALID);
        }

        $params['requestExpiresAt'] = (int)$params['requestExpiresAt'];
        $publicKey                  = $this->state->get('oxygen_public_key');

        if (!$publicKey) {
            throw new Oxygen_Exception(Oxygen_Exception::PUBLIC_KEY_MISSING);
        }

        if (!$this->rsaVerifier->verify($publicKey, sprintf('%s|%d|%s|%s', $params['oxygenRequestId'], $params['requestExpiresAt'], $params['userUid'], $params['username']), $params['signature'])) {
            throw new Oxygen_Exception(Oxygen_Exception::HANDSHAKE_VERIFY_FAILED);
        }

        $this->nonceManager->useNonce($params['oxygenRequestId'], $params['requestExpiresAt']);

        $closure = new Oxygen_Util_Closure(array($this, 'loginUser'), $params['userUid'], $params['username']);

        $event->setDeferredResponse(new Oxygen_Util_HookedClosure('init', $closure->getCallable()));
    }

    /**
     * @internal
     *
     * @param string $userUid UID of the user on the management dashboard, not a Drupal's user.
     * @param string $username
     *
     * @return Oxygen_Http_RedirectResponse
     *
     * @throws Oxygen_Exception
     */
    public function loginUser($userUid, $username)
    {
        if (strlen($username)) {
            $user = $this->userManager->findUserByUsername($username);
        } else {
            $user = $this->userManager->findUserById(1);
        }

        if ($user === null) {
            throw new Oxygen_Exception(Oxygen_Exception::AUTO_LOGIN_CAN_NOT_FIND_USER, null, array(
                'username' => $username,
            ));
        }

        $this->sessionManager->userLogin($user);
        $options          = array();
        $httpResponseCode = 302;

        $sessionId = $this->sessionManager->getSessionId();

        $this->sessionManager->registerSession($userUid, $sessionId);

        $path = 'admin/dashboard';
        $this->context->alter('drupal_goto', $path, $options, $httpResponseCode);
        $options = array('absolute' => true);

        // The 'Location' HTTP header must be absolute.
        $options['absolute'] = true;
        $url                 = $this->context->url($path, $options);

        $response = new Oxygen_Http_RedirectResponse($url, $httpResponseCode);

        return $response;
    }
}
