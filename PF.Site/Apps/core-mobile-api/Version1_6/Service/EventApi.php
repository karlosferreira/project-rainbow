<?php /** @noinspection ALL */

/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_6\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Security\Event\EventAccessControl;
use Apps\Core_MobileApi\Version1_6\Api\Form\Event\EventForm;
use Phpfox;


class EventApi extends \Apps\Core_MobileApi\Service\EventApi
{

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class, [
            'title'  => 'create_new_event',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('event')
        ]);
        $form->setCategories($this->getCategories());
        $event = $this->loadResourceById($editId, true);
        if ($editId && empty($event)) {
            return $this->notFoundError();
        }

        if ($event) {
            $this->denyAccessUnlessGranted(EventAccessControl::EDIT, $event);
            $form->setTitle('edit_event')
                ->setAction(UrlUtility::makeApiUrl('event/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($event);
        } else {
            $this->denyAccessUnlessGranted(EventAccessControl::ADD);
            if (($iFlood = $this->getSetting()->getUserSetting('event.flood_control_events')) !== 0) {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('event'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('you_are_creating_an_event_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
        }

        return $this->success($form->getFormStructure());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(EventAccessControl::ADD);
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventResource::populate([])->getResourceName()
                ], [], $this->localization->translate('event_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var EventForm $form */
        $form = $this->createForm(EventForm::class);
        $event = $this->loadResourceById($id, true);
        if (empty($event)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(EventAccessControl::EDIT, $event);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventResource::populate([])->getResourceName()
                ], [], $this->localization->translate('event_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }
}