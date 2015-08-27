<?php

final class Oxygen_Event_Events
{
    /**
     * The request in question is a regular HTTP request.
     * Can be used for analytics.
     *
     * @see Oxygen_Event_PublicRequestEvent
     */
    const PUBLIC_REQUEST = 'public_request';

    /**
     * The request in question wants to trigger the Oxygen module.
     * Can be used for authentication.
     *
     * @see Oxygen_Event_MasterRequestEvent
     */
    const MASTER_REQUEST = 'master_request';

    /**
     * The action callback is not executed immediately, but after further bootstrapping.
     *
     * @see Oxygen_Event_DelayedActionEvent
     */
    const DELAYED_ACTION = 'delayed_action';

    /**
     * The action returned data array instead of a response. This is more common.
     * Can be used to attach data like logs.
     *
     * @see Oxygen_Event_ActionDataEvent
     */
    const ACTION_DATA = 'action_data';

    /**
     * The response is preparing to be sent.
     * Can be used as a last resort to attach data to it.
     *
     * @see Oxygen_Event_MasterResponseEvent
     */
    const MASTER_RESPONSE = 'master_response';

    /**
     * There was an exception during execution.
     * Can be used to attach error details. Pay attention to whom it is shown!
     *
     * @see Oxygen_Event_ExceptionEvent
     */
    const EXCEPTION = 'exception';

    private function __construct()
    {
    }
}
