<?php

function oxygen_admin_disconnect()
{
    $form['container'] = array(
        '#type'            => 'container',
        'status'           => array(
            '#markup' => variable_get('oxygen_public_key') ? t("The module is connected to a dashboard.") : t("The module is not connected to a dashboard."),
        ),
        'oxygen_connected' => array(
            '#type'  => 'hidden',
            '#value' => variable_get('oxygen_public_key') ? 'yes' : 'no',
        ),
    );
    if (variable_get('oxygen_public_key')) {
        $form['actions'] = array(
            '#type'       => 'container',
            '#attributes' => array('class' => array('form-actions')),
            'disconnect'  => array(
                '#type'  => 'submit',
                '#value' => t("Disconnect Oxygen module"),
                '#name'  => 'oxygen_disconnect',
            ),
        );
    }

    return $form;
}

function oxygen_admin_disconnect_submit()
{
    variable_del('oxygen_public_key');
}
