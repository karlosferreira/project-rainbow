<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:51 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Service\CoreApi;
use Phpfox;

class CommentResource extends ResourceBase
{
    const RESOURCE_NAME = "comment";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'comment';
    public $item_type;
    public $item_id;

    public $parent_id;
    public $child_total;
    public $like_type_id = 'feed_mini';

    /**
     * @var CommentResource[]
     */
    public $children = [];

    public $statistic;

    public $is_liked;

    /**
     * @var UserResource who make the comment
     */
    public $user;

    public $text;

    public $can_delete = null;

    public $extra_data;
    public $is_hidden;
    public $total_hidden;
    public $hide_ids;
    public $hide_this;

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getItemType()
    {
        return $this->rawData['type_id'];
    }

    public function getIsLiked()
    {
        return (!empty($this->rawData['is_liked']) ? true : false);
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['text_parsed'])) {
            $this->text = $this->rawData['text_parsed'];
        }
        $this->text = $this->parse->parseOutput($this->text, false);
        $this->text = $this->parseMention($this->text);
        $this->text = $this->parsedEmoji($this->text);
        $this->text = preg_replace('/<a[^>]*\sclass="site_hash_tag">([^<]+)?<\/a>/', '$1', $this->text);
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->item_id;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_type', ['type' => ResourceMetadata::STRING])
            ->mapField('parent_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('child_total', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('hide_this', ['type' => ResourceMetadata::BOOL])
            ->mapField('total_hidden', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_hidden', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => 'comment',
            'schema'        => [
                'definition' => [
                    'children' => 'comment[]'
                ]
            ],
            'list_view'     => [
                'item_view'     => 'comment',
                'limit'         => 20,
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-comment'),
                    'label'     => $l->translate('no_comments_yet'),
                    'sub_label' => $l->translate('be_the_first_to_comment')
                ],
            ],
            'detail_view'   => [
                'component_name' => 'comment_detail',
            ],
            'action_menu'   => [
                ['label' => $l->translate('hide'), 'value' => '@comment/hide', 'style' => '', 'acl' => 'can_hide', 'show' => '!is_hidden'],
                ['label' => $l->translate('unhide'), 'value' => '@comment/un_hide', 'style' => '', 'acl' => 'can_hide', 'show' => 'is_hidden'],
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report'],
                ['label' => $l->translate('edit'), 'value' => '@comment/edit', 'style' => '', 'acl' => 'can_edit', 'show' => '!is_hidden'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_COMMENT, 'style' => 'danger', 'acl' => 'can_delete']
            ],
        ]);
    }

    public function setStatistic($statistic)
    {
        $statistic->child_total = isset($this->rawData['child_total']) ? (int)$this->rawData['child_total'] : 0;
        $this->statistic = $statistic;
    }

    public function getCanDelete()
    {
        if (Phpfox::getUserParam('comment.can_delete_comment_on_own_item') && $this->can_delete === null) {
            if ($this->getItemType() == 'feed') {
                db()->join(Phpfox::getT('feed_comment'), 'fc', 'fc.feed_comment_id = c1.item_id');
            } else {
                db()->join(Phpfox::getT('feed'), 'fc',
                    'c1.type_id = fc.type_id AND c1.item_id = fc.item_id');
            }
            $parent = db()->select('fc.parent_user_id, c1.owner_user_id')
                ->from(Phpfox::getT('comment'), 'c1')
                ->where('c1.comment_id = ' . (int)$this->getId())
                ->execute('getSlaveRow');

            $canDelete = false;
            if (isset($parent['parent_user_id']) && $parent['parent_user_id'] == Phpfox::getUserId()) {
                $canDelete = true;
            } else if (isset($parent['owner_user_id']) && $parent['owner_user_id'] == Phpfox::getUserId()) {
                $canDelete = true;
            }
            $this->can_delete = $canDelete;
        }
        return $this->can_delete;
    }

    public function getExtraData()
    {
        if (!empty($this->extra_data)) {
            $extra = $this->extra_data;
            if ($extra['extra_type'] == 'photo') {
                $fullPath = Image::createFrom([
                    'file'      => 'comment/' . $extra['image_path'],
                    'server_id' => $extra['server_id'],
                    'path'      => 'core.url_pic',
                    'suffix'    => '_500'
                ])->image_url;
            } else {
                if (!empty($extra['full_path'])) {
                    if (strpos($extra['full_path'], 'data-src') !== false) {
                        $fullPath = preg_replace('/<*img[^>]*data-src*=*["\']([^"\']*)?["\'].*/', '$1', $extra['full_path']);
                    } else {
                        $fullPath = preg_replace('/<*img[^>]*src*=*["\']([^"\']*)?["\'].*/', '$1', $extra['full_path']);
                    }
                } else {
                    $fullPath = '';
                }
            }
            if (!empty($extra['params']['title'])) {
                $extra['params']['title'] = $this->parse->cleanOutput($extra['params']['title']);
            }
            if (!empty($extra['params']['description'])) {
                $extra['params']['description'] = TextFilter::pureText($extra['params']['description'], 255, true);
            }
            $this->extra_data = array_merge($extra, [
                'extra_id' => (int)$extra['extra_id'],
                'comment_id' => (int)$extra['comment_id'],
                'server_id' => (int)$extra['server_id'],
                'is_deleted' => !!$extra['is_deleted'],
                'full_path' => $fullPath
            ]);
        }
        return $this->extra_data;
    }

    protected function parsedEmoji($sTxt)
    {
        if (!class_exists('Apps\Core_Comments\Service\Stickers\Stickers')) {
            return $sTxt;
        }
        $aEmojis = Phpfox::getService('comment.emoticon')->getAll();

        /*parse emojis*/
        $bHasUnicode = false;
        $aListUnicode = [
            '(waving)' => '\uD83D\uDC4B',
            '(OK)' => '\uD83D\uDC4C',
            '(y)' => '\uD83D\uDC4D',
            '(n)' => '\uD83D\uDC4E',
            '(clap)' => '\uD83D\uDC4F',
            '(smiling)' => '\uD83D\uDE0A',
            '(savoring)' => '\uD83D\uDE0B',
            '(relieved)' => '\uD83D\uDE0C',
            '(hearteyes)' => '\uD83D\uDE0D',
            '(cool)' => '\uD83D\uDE0E',
            '(smirking)' => '\uD83D\uDE0F',
            '(kiss)' => '\uD83D\uDE1A',
            ':P' => '\uD83D\uDE0B',
            '(disappointed)' => '\uD83D\uDE1E',
            ':S' => '\uD83D\uDE1F',
            '(sleepy)' => '\uD83D\uDE2A',
            '(cryingface)' => '\uD83D\uDE22',
            ';(' => '\uD83D\uDE2D',
            ':O' => '\uD83D\uDE32',
            '(handshake)' => '\uD83E\uDD1D',
            '(rockon)' => '\uD83E\uDD1F',
            '(zany)' => '\uD83D\uDE1C',
            '(shush)' => '\uD83E\uDD2B',
            '(chuckle)' => '\uD83E\uDD2D',
            '(puke)' => '\uD83E\uDD2E',
            '(brokenheart)' => '\uD83D\uDC94',
            ':D' => '\uD83D\uDE00',
            '(beaming)' => '\uD83D\uDE01',
            '(tearofjoys)' => '\uD83D\uDE02',
            '(smilingeyes)' => '\uD83D\uDE04',
            '(sweat)' => '\uD83D\uDE05',
            '(squint)' => '\uD83D\uDE06',
            '(angel)' => '\uD83D\uDE07',
            '(devil)' => '\uD83D\uDE08',
            ';)' => '\uD83D\uDE09',
            ':|' => '\uD83D\uDE10',
            '(expressionless)' => '\uD83D\uDE11',
            '(unamused)' => '\uD83D\uDE12',
            '(downcast)' => '\uD83D\uDE13',
            ':(' => '\uD83D\uDE41',
            ':-/' => '\uD83D\uDE15',
            '(confounded)' => '\uD83D\uDE16',
            '(kissingface)' => '\uD83D\uDE17',
            ':-*' => '\uD83D\uDE18',
            '(angry)' => '\uD83D\uDE20',
            '(pounting)' => '\uD83D\uDE21',
            '(persevering)' => '\uD83D\uDE23',
            '(steamnose)' => '\uD83D\uDE24',
            '(anguished)' => '\uD83D\uDE27',
            '(fearful)' => '\uD83D\uDE28',
            '(weary)' => '\uD83D\uDE29',
            '(anxious)' => '\uD83D\uDE30',
            '(scream)' => '\uD83D\uDE31',
            '(sleep)' => '\uD83D\uDE34',
            '(dizzy)' => '\uD83D\uDE35',
            '(emptymouth)' => '\uD83D\uDE36',
            '(medicalmask)' => '\uD83D\uDE37',
            '(frown)' => '\uD83D\uDE26',
            ':)' => '\uD83D\uDE42',
            '(upsidedown)' => '\uD83D\uDE43',
            '(rollingeyes)' => '\uD83D\uDE44',
            '(zipper)' => '\uD83E\uDD10',
            '(moneymouth)' => '\uD83E\uDD11',
            '(sick)' => '\uD83E\uDD12',
            '(nerd)' => '\uD83E\uDD13',
            '(think)' => '\uD83E\uDD14',
            '(injure)' => '\uD83E\uDD15',
            '(hug)' => '\uD83E\uDD17',
            '(nauseated)' => '\uD83E\uDD22',
            '(drooling)' => '\uD83E\uDD24',
            '(lie)' => '\uD83E\uDD25',
            '(sneeze)' => '\uD83E\uDD27',
            '(starstruck)' => '\uD83E\uDD29',
            '(star)' => '\uD83C\uDF1F',
            '(victory)' => '\u270C',
            '(heart)' => '\u2764',
        ];
        foreach ($aEmojis as $aEmoji) {
            if($bHasUnicode || !empty($aEmoji['unicode'])) {
                $bHasUnicode = true;
                $sTxt = str_replace($aEmoji['code'], json_decode('"'.$aEmoji['unicode'].'"'), $sTxt);
            } else {

                $sTxt = str_replace($aEmoji['code'], isset($aListUnicode[$aEmoji['code']]) ? json_encode('"'.$aListUnicode[$aEmoji['code']].'"') : $aEmoji['code'], $sTxt);
            }
        }
        return $sTxt;
    }

    protected function parseMention($text)
    {
        // Parse groups/pages mentions
        if (Phpfox::isModule('groups')) {
            $text = preg_replace_callback('/\[group=(\d+)\].+?\[\/group\]/u', function ($matches) {
                return Phpfox::getService(ParseInterface::class)->parseGroupMention($matches[1]);
            }, $text);
        }
        if (Phpfox::isModule('pages')) {
            $text = preg_replace_callback('/\[page=(\d+)\].+?\[\/page\]/u', function ($matches) {
                return Phpfox::getService(ParseInterface::class)->parsePageMention($matches[1]);
            }, $text);
        }
        return $text;
    }

    public function getUrlMapping($url, $queryArray)
    {
        $result = $url;
        preg_match('/comment\/view\/(\d+)?/', $result, $match);
        if (!empty($match[1])) {
            if (Phpfox::hasCallback('comment', 'getRedirectRequest')) {
                $finalUrl = Phpfox::callback('comment.getRedirectRequest', $match[1]);
                $result = (new CoreApi())->parseUrlToRoute($finalUrl, true);
                $queryArray = isset($result['params']['query']) ? $result['params']['query'] : [];
                if (!empty($result['params'])) {
                    $result['params']['query'] = array_merge($queryArray, ['comment_id' => (int)$match[1]]);
                }
            }
        }
        return $result;
    }

    public function toArray($displayFields = null)
    {
        $data = parent::toArray($displayFields);
        if (!empty($this->rawData['ignore_child'])) {
            unset($data['children']);
        }
        return $data;
    }
}