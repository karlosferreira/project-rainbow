<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 20/4/18
 * Time: 5:44 PM
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\AttachmentResource;
use Apps\Core_MobileApi\Api\Security\Attachment\AttachmentAccessControl;
use Phpfox;

class AttachmentApi extends AbstractResourceApi
{

    /**
     * @var \Attachment_Service_Attachment
     */
    private $attachmentService;

    public function __construct()
    {
        parent::__construct();
        $this->attachmentService = Phpfox::getService("attachment");
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['item_id', 'item_type', 'profile_id'])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('profile_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if (!$this->getUser()->getId()) {
            return $this->permissionError();
        }

        $this->denyAccessUnlessGranted(AttachmentAccessControl::VIEW);

        $attachments = $this->getAttachmentsBy($params['item_id'], $params['item_type'], $params['profile_id']);

        return $this->success($attachments);
    }

    /**
     * Get Attachment By Item Id and Item Type
     *
     * @param int  $itemId Item ID
     * @param      $itemType
     * @param null $profileId
     *
     * @return array|void
     */
    public function getAttachmentsBy($itemId, $itemType, $profileId = null)
    {
        if ($profileId) {
            if (!$this->getUser()->getId()) {
                return $this->permissionError();
            }
            $conds = [
                'attachment.user_id = ' . (int)$profileId,
                // 'AND attachment.item_id > 0'
            ];
        } else if (!empty($itemId) && !empty($itemType)) {
            if (!$this->getUser()->getId()) {
                return $this->permissionError();
            }
            $conds = [
                'attachment.item_id = ' . (int)$itemId,
                'AND attachment.category_id = "' . $itemType . '"'
            ];
        } else {
            $conds = [
                'attachment.user_id = ' . (int)$this->getUser()->getId()
            ];
        }

        list(, $attachments) = $this->attachmentService->get($conds, false);
        $attachments = array_map(function ($item) {
            return AttachmentResource::populate($item)->displayShortFields()->toArray();
        }, $attachments);

        return $attachments;
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        $params = $this->resolver->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $attachment = $this->attachmentService->getForDownload($params['id']);

        if (empty($attachment)) {
            return $this->notFoundError();
        }
        $attachment = AttachmentResource::populate($attachment);
        $this->denyAccessUnlessGranted(AttachmentAccessControl::VIEW, $attachment);

        return $this->success($attachment->toArray());
    }

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $params = $this->resolver->setDefined(['custom_attachment', 'item_id', 'input', 'file_type'])
            ->setRequired(['item_type'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $result = $this->processCreate($params);
        if ($this->isPassed()) {
            return $this->success($result);
        }
        return $this->error($this->getErrorMessage());
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $params = $this->resolver->setRequired(['id'])
            ->setAllowedTypes('id', 'int')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $attachment = $this->loadResourceById($params['id'], true);
        if (empty($attachment)) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(AttachmentAccessControl::DELETE, $attachment);
        $iUserId = $this->attachmentService->hasAccess($params['id'], 'delete_own_attachment',
            'delete_user_attachment');

        $this->processService()->delete($iUserId, $attachment->getId());

        if (!$this->isPassed()) {
            return $this->error($this->getErrorMessage());
        }

        return $this->success([], [], $this->getLocalization()->translate('attachment_deleted_successfully'));

    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $resource = false)
    {
        $attachment = $this->database()->select("*")
            ->from(":attachment")
            ->where('attachment_id = ' . (int)$id)
            ->execute("getRow");
        if (empty($attachment)) {
            return null;
        }
        if ($resource) {
            $attachment = AttachmentResource::populate($attachment);
        }
        return $attachment;
    }

    private function processCreate($params)
    {
        if (!isset($_FILES['file']) && isset($_FILES['Filedata'])) {
            $_FILES['file'] = [];
            $_FILES['file']['error']['file'] = UPLOAD_ERR_OK;
            $_FILES['file']['name']['file'] = $_FILES['Filedata']['name'];
            $_FILES['file']['type']['file'] = $_FILES['Filedata']['type'];
            $_FILES['file']['tmp_name']['file'] = $_FILES['Filedata']['tmp_name'];
            $_FILES['file']['size']['file'] = $_FILES['Filedata']['size'];
        } else if (!isset($_FILES['file'])) {
            return \Phpfox_Error::set("'file' is required");
        }

        $oFile = \Phpfox_File::instance();
        $oImage = \Phpfox_Image::instance();
        $oAttachment = Phpfox::getService('attachment.process');
        $sIds = '';
        $iUploaded = 0;
        $iFileSizes = 0;

        $success = [];
        foreach ($_FILES['file']['error'] as $iKey => $sError) {
            if ($sError == UPLOAD_ERR_OK) {
                $aValid = [];
                if ($params['file_type'] == 'photo') {
                    $aValid = ['gif', 'png', 'jpg'];
                }

                if ($params['input'] == '' && $params['file_type'] == '') {
                    $aValid = Phpfox::getService('attachment.type')->getTypes();
                }

                if (empty($aValid)) {
                    return $this->error($this->getLocalization()->translate('attachment_does_not_support_any_extension'));
                }

                $iMaxSize = null;

                if (Phpfox::getUserParam('attachment.item_max_upload_size') !== 0) {
                    $iMaxSize = (Phpfox::getUserParam('attachment.item_max_upload_size') / 1024);
                }

                $aImage = $oFile->load('file[' . $iKey . ']', $aValid, $iMaxSize);

                if (empty($aImage['ext'])) {
                    return $this->error($this->getErrorMessage());
                }

                $iUploaded++;
                $bIsImage = in_array($aImage['ext'], Phpfox::getParam('attachment.attachment_valid_images'));
                $params['file_type'] = 'photo';

                $iId = $oAttachment->add([
                        'category'  => $params['item_type'],
                        'file_name' => $_FILES['file']['name'][$iKey],
                        'extension' => $aImage['ext'],
                        'is_image'  => $bIsImage
                    ]
                );

                $sIds .= $iId . ',';

                $sFileName = $oFile->upload('file[' . $iKey . ']', Phpfox::getParam('core.dir_attachment'), $iId);
                if (Phpfox::isAppActive('Core_Photos')) {
                    Phpfox::getService('photo')->cropMaxWidth(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName,
                            ''));
                }
                $sFileSize = filesize(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''));
                $iFileSizes += $sFileSize;

                $oAttachment->update([
                    'file_size'   => $sFileSize,
                    'destination' => $sFileName,
                    'server_id'   => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                ], $iId);

                if ($bIsImage) {
                    $sThumbnail = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_thumb');
                    $sViewImage = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_view');

                    $oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''),
                        $sThumbnail, Phpfox::getParam('attachment.attachment_max_thumbnail'),
                        Phpfox::getParam('attachment.attachment_max_thumbnail'));
                    $oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''),
                        $sViewImage, Phpfox::getParam('attachment.attachment_max_medium'),
                        Phpfox::getParam('attachment.attachment_max_medium'));

                    $iFileSizes += (filesize($sThumbnail) + filesize($sThumbnail));
                }

                if ($params['file_type'] == 'photo') {
                    $aAttachment = $this->database()->select('*')
                        ->from(Phpfox::getT('attachment'))
                        ->where('attachment_id = ' . (int)$iId)
                        ->execute('getRow');

                    $fileUrl = Phpfox::getLib('image.helper')->display([
                        'server_id'  => $aAttachment['server_id'],
                        'path'       => 'core.url_attachment',
                        'file'       => $aAttachment['destination'],
                        'suffix'     => '_view',
                        'return_url' => true
                    ]);

                } else {
                    $fileUrl = Phpfox::getLib('image.helper')->display([
                        'server_id'  => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                        'path'       => 'core.url_attachment',
                        'file'       => $sFileName['destination'],
                        'suffix'     => '_view',
                        'return_url' => true
                    ]);
                }

                // Update user space usage
                Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'attachment', $iFileSizes);
                $success[] = [
                    'id'  => $iId,
                    'url' => $fileUrl
                ];
            }
        }

        return $success;

    }

    public function createAccessControl()
    {
        $this->accessControl = new AttachmentAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * @return \Attachment_Service_Process
     */
    private function processService()
    {
        return Phpfox::getService('attachment.process');
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}