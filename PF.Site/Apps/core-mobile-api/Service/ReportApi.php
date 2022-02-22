<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Report\ReportForm;
use Apps\Core_MobileApi\Api\Resource\ReportResource;
use Phpfox;
use Report_Service_Report;

class ReportApi extends AbstractApi
{
    const REPORT_FORM_ROUTE = "report/form";
    const REPORT_ROUTE = "report";

    public function __naming()
    {
        return [
            self::REPORT_FORM_ROUTE => [
                'get' => 'form'
            ],
            self::REPORT_ROUTE      => [
                'post' => 'report'
            ]
        ];
    }

    public function form($params = [])
    {
        $params = $this->resolver->setRequired(['item_id', 'item_type'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getService('report')->canReport($params['item_type'], $params['item_id'])) {
            return $this->error($this->getLocalization()->translate('you_have_already_reported_this_item'));
        }
        /** @var ReportForm $form */
        $form = $this->createForm(ReportForm::class, [
            'title'       => 'report',
            'method'      => 'post',
            'action'      => UrlUtility::makeApiUrl(self::REPORT_ROUTE),
            'description' => $this->getLocalization()
                ->translate('you_are_about_to_report_a_violation_of_our_a_href_link_target_blank_terms_of_use_a', [
                    'link' => $this->makeUrl('terms')
                ])
        ]);
        $form->setReasonOptions($this->getReportService()->getOptions($params['item_type']));

        return $this->success($form->getFormStructure());
    }

    public function report($params)
    {
        $params = $this->resolver->setRequired(['item_type'])
            ->resolve($params)
            ->getParameters();
        /** @var ReportForm $form */
        $form = $this->createForm(ReportForm::class);
        $form->setReasonOptions($this->getReportService()->getOptions($params['item_type']));
        if ($form->isValid() && ($values = $form->getValues())) {
            if (!Phpfox::getService('report')->canReport($values['item_type'], $values['item_id'])) {
                return $this->error($this->getLocalization()->translate('you_have_already_reported_this_item'));
            }
            $result = Phpfox::getService('report.data.process')
                ->add($values['reason'], $values['item_type'], $values['item_id'], $values['feedback']);
            if ($result && $this->isPassed()) {
                return $this->success(['report' => $result]);
            }
            return $this->error($this->getErrorMessage());
        }

        return $this->validationParamsError($form->getInvalidFields());
    }


    /**
     * @return Report_Service_Report|object
     */
    private function getReportService()
    {
        return Phpfox::getService("report");
    }

    /**
     * @param $param
     *
     * @return MobileApp
     */
    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('report', [
            'title'         => $l->translate('Reports'),
            'main_resource' => new ReportResource([]),
        ]);
        return $app;
    }

}