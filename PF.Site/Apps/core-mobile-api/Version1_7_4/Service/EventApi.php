<?php
namespace Apps\Core_MobileApi\Version1_7_4\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Security\Event\EventAccessControl;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Service\EventApi as BaseEventApi;
use Apps\Core_MobileApi\Version1_7_4\Api\Form\Event\EventForm;
use Phpfox;

class EventApi extends BaseEventApi
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
            $form->setEditing(true);
            $iRepeat = db()->select('is_repeat')->from(':event')
                ->where(['event_id' => $editId])
                ->executeField();
            if ($iRepeat != '-1') {
               $form->setIsRepeat(true);
            }
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
        $form->setEditing(true);
        $iRepeat = db()->select('is_repeat')->from(':event')
            ->where(['event_id' => $id])
            ->executeField();
        if ($iRepeat != '-1') {
            $form->setIsRepeat(true);
        }
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

    /**
     * @param $values
     *
     * @return int
     */
    protected function processCreate($values)
    {
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

        $this->convertSubmitForm($values);
        return $this->processService->add($values, $values['module_id'], $values['item_id']);
    }

    /**
     * @param $id
     * @param $values
     *
     * @return bool
     */
    protected function processUpdate($id, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['event_id'] = $id;
        return $this->processService->update($id, $values);
    }

    /**
     * Get for display on activity feed
     *
     * @param array $feed
     * @param array $item detail data from database
     *
     * @return array
     */
    function getFeedDisplay($feed, $item)
    {
        if (empty($item) && !$item = $this->loadResourceById($feed['item_id'])) {
            return null;
        }
        $event = EventResource::populate($item)->getFeedDisplay();
        $event['time_format'] = Phpfox::getParam('event.event_time_format');
        $aEvent = $this->eventService->getEventSimple($item['event_id']);
        $event['is_online'] = $aEvent['is_online'];
        $event['online_link'] = !empty($aEvent['online_link']) ? $aEvent['online_link'] : '';
        return $event;
    }

    protected function convertSubmitForm(&$vals, $edit = false)
    {
        if (isset($vals['categories'])) {
            $vals['category'] = $vals['categories'];
        }
        if (!$edit) {
            if (!isset($vals['isrepeat'])) {
                $vals['isrepeat'] = '-1';
            } else {
                if($vals['isrepeat'] != -1) {
                    if ($vals['repeat_type'] == 0) {
                        if (empty($vals['after_number_event'])) {
                            return $this->error($this->getLocalization()->translate('you_must_fill_one_of_2_repeated_fields'));
                        }
                        if (Phpfox::getUserParam('event.max_events_created')) {
                            $iRemainingEvent = (int)Phpfox::getUserParam('max_events_created') - Phpfox::getService('event')->getMyTotal();
                            if ((int)$vals['after_number_event'] >= $iRemainingEvent) {
                                return $this->error($this->getLocalization()->translate('you_can_only_create_num_events_more', ['num' => $iRemainingEvent]));
                            }
                        }
                        $vals['repeat_section_end_repeat'] = 'after_number_event';
                        $vals['repeat_section_after_number_event'] = $vals['after_number_event'];
                    } else {
                        if (empty($vals['timerepeat'])) {
                            return $this->error($this->getLocalization()->translate('you_must_fill_one_of_2_repeated_fields'));
                        }
                        $vals['repeat_section_end_repeat'] = 'repeat_until';

                        $timeRepeat = (new \DateTime($vals['timerepeat']));
                        $vals['repeat_section_repeatuntil_month'] = $timeRepeat->format('m');
                        $vals['repeat_section_repeatuntil_day'] = $timeRepeat->format('d');
                        $vals['repeat_section_repeatuntil_year'] = $timeRepeat->format('Y');
                    }
                }
            }
        }

        if($vals['is_online'] == 0) {
            unset($vals['is_online']);
            $vals['online_link'] = '';
            $vals['location'] = $vals['location_req']['location'];
            $vals['location_lat'] = $vals['location_req']['location_lat'];
            $vals['location_lng'] = $vals['location_req']['location_lng'];
            $vals['country_iso'] = $vals['location_req']['country_iso'];
        } else {
            $vals['location'] = $vals['location_non_req']['location'];
            $vals['location_lat'] = $vals['location_non_req']['location_lat'];
            $vals['location_lng'] = $vals['location_non_req']['location_lng'];
            $vals['country_iso'] = $vals['location_non_req']['country_iso'];
        }

        $inValid = [];
        $startTime = (new \DateTime($vals['start_time']));
        if (empty($startTime)) {
            $inValid[] = 'start_time';
        }

        $endTime = (new \DateTime($vals['end_time']));
        if (empty($endTime)) {
            $inValid[] = 'end_time';
        }
        if (!empty($inValid)) {
            return $this->validationParamsError($inValid);
        }
        $vals['start_month'] = $startTime->format('m');
        $vals['start_day'] = $startTime->format('d');
        $vals['start_year'] = $startTime->format('Y');
        $vals['start_hour'] = $startTime->format('H');
        $vals['start_minute'] = $startTime->format('i');

        $vals['end_month'] = $endTime->format('m');
        $vals['end_day'] = $endTime->format('d');
        $vals['end_year'] = $endTime->format('Y');
        $vals['end_hour'] = $endTime->format('H');
        $vals['end_minute'] = $endTime->format('i');
        if (!empty($vals['text'])) {
            $vals['description'] = $vals['text'];
        } else {
            $vals['description'] = '';
        }
        if (!empty($vals['file'])) {
            if (!$edit) {
                if (!empty($vals['file']['temp_file'])) {
                    $vals['temp_file'] = $vals['file']['temp_file'];
                }
            } else {
                if ($vals['file']['status'] == FileType::NEW_UPLOAD || $vals['file']['status'] == FileType::CHANGE) {
                    $vals['temp_file'] = $vals['file']['temp_file'];
                } else if ($vals['file']['status'] == FileType::REMOVE) {
                    $vals['remove_photo'] = 1;
                }
            }
        }
        if (!$edit) {
            if (empty($vals['module_id'])) {
                $vals['module_id'] = 'event';
            }
            if (empty($vals['item_id'])) {
                $vals['item_id'] = 0;
            }
        }
        if (!empty($vals['attachment'])) {
            $vals['attachment'] = implode(",", $vals['attachment']);
        }
    }
}