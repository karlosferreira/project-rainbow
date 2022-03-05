<?php

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\SettingParametersBag;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\UndefinedResourceName;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Coordinate;
use Apps\Core_MobileApi\Api\Resource\Object\FeedParam;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Api\ResourceInterface;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class ResourceBase
 *
 * Public properties will be auto generate to response Array
 *
 * @package Apps\Core_MobileApi\Api\Resources
 */
abstract class ResourceBase
{
    const VIEW_DEFAULT = "default";
    const VIEW_LIST = "list";
    const VIEW_DETAIL = "detail";

    // Control the state of the object to know what to do next
    const STATE_POPULATED = "POPULATED";
    const STATE_INIT = "INIT";

    const NAMING_RESOURCE_OWNER = "user";
    const NAMING_RESOURCE_OWNER_ID = "user_id";

    // Statistic show general info about total_like, total_comment...
    const NAMING_PROPERTY_STATISTIC = "statistic";

    //Statistic show info location lat/long
    const NAMING_PROPERTY_COORDINATE = "coordinate";

    /**
     * Required name of the resource
     *
     * @var string
     */
    public $resource_name;
    public $module_name;

    /**
     * @var string Override `id field` for mapping into `id` property
     */
    protected $idFieldName;

    public $id;
    public $creation_date;
    public $modification_date;
    public $link;

    public $extra;

    public $privacy = 0;

    /**
     * @var HyperLink[] show referral api of current
     */
//    public $self;

    /**
     * @var HyperLink[] Show related resource of current
     */
//    public $links;

    /**
     * @var array
     */
    protected $rawData;

    /**
     * @var FeedParam
     */
    protected $feed_param;

    protected $load_feed_param = false;


    private $state = self::STATE_INIT;


    /**
     * @var array list of fields will display when convert to array, default all public fields
     */
    protected $displayFields = [];

    protected $isDisplayShortField = false;

    protected $viewMode = self::VIEW_DEFAULT;

    /**
     * @var AccessControl control permission
     */
    protected $accessControl;

    /**
     * @var ResourceMetadata manage metadata definition
     */
    protected $metadata;

    protected $parse;

    protected $localization;

    protected $coordinateMapping;

    /**
     * ResourceBase constructor.
     *
     * @param $data
     *
     * @throws UndefinedResourceName
     */
    public function __construct($data)
    {
        if (empty($this->resource_name)) {
            throw new UndefinedResourceName();
        }
        $this->parse = \Phpfox::getService(ParseInterface::class);
        $this->rawData = $data;
        $this->loadMetadataSchema();
        $this->autoMapToProperties();
    }

    /**
     * @param $data
     *
     * @return static
     * @throws UndefinedResourceName
     */
    public static function populate($data)
    {
        return new static($data);
    }

    /**
     * Convert Resource to array
     *
     * @param array|null $displayFields List of fields will be generated
     *
     * @return array
     * @internal param array|null $data
     */
    public function toArray($displayFields = null)
    {
        if (empty($displayFields) && !empty($this->displayFields)) {
            $displayFields = $this->displayFields;
        }
        return $this->autoGenerateArray($displayFields);
    }

    /**
     * Generate properties to Array base ob public resource
     *
     * @param array|null $displayFields
     *
     * @return array
     */
    protected function autoGenerateArray($displayFields = null)
    {
        if (empty($this->rawData)) {
            return [];
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.api_resource_base_generate_array_start')) ? eval($sPlugin) : false);

        $result = [];
        $properties = $this->getAllFields();
        foreach ($properties as $property) {

            if (!empty($displayFields) && !in_array($property, $displayFields)) {
                continue;
            }
            //Remove links and self because useless
            if (in_array($property, ['links', 'self'])) {
                continue;
            }

            $value = $this->__getProperty($property);
            $name = $property;
            if (is_object($value) && $value instanceof ResourceBase) {
                $result[$name] = $value->toArray($value->getShortFields());
            } else if (is_array($value) && isset($value[0]) && $value[0] instanceof ResourceBase) {
                // A collection of resources
                $result[$name] = array_map(
                    function ($res) {
                        return $res->toArray();
                    }, $value);
            } else if (is_object($value) && method_exists($value, "toArray")) {
                $result[$name] = $value->toArray();
            } else {
                $result[$name] = $value;
            }
        }
        // Load activity Feed parameters
        if ($this->load_feed_param) {
            $result['feed_param'] = $this->getFeedParam();
            if (!empty($result['feed_param']) && is_array($result['feed_param'])) {
                $likes = Phpfox::getService('like')->getAll($result['feed_param']['like_type_id'], $result['feed_param']['item_id'], (isset($this->rawData['feed_table_prefix']) ? $this->rawData['feed_table_prefix'] : ''));
                if (isset($likes['likes']['phrase'])) {
                    $result['feed_param']['like_phrase'] = html_entity_decode($likes['likes']['phrase'], ENT_QUOTES);
                }
            }
        }

        if (is_array($displayFields)) {
            // Safe case to get field's data base on name
            foreach ($displayFields as $field) {
                if (!isset($result[$field]) && !empty($this->rawData[$field])) {
                    $result[$field] = $this->rawData[$field];
                }
            }
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.api_resource_base_generate_array_end')) ? eval($sPlugin) : false);

        return $result;
    }

    /**
     * Get All public fields for generating array
     */
    public function getAllFields()
    {
        $reflection = new ReflectionObject($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        $result = [];
        foreach ($properties as $property) {
            $result[] = $property->getName();
        }
        return $result;
    }

    /**
     * Default implementation for listing of sub resource object
     * This function call when generate sub resource object
     * Override this function to control output
     *
     * @return array
     */
    public function getShortFields()
    {
        return $this->getAllFields();
    }

    public function displayShortFields()
    {
        $this->isDisplayShortField = true;
        return $this->setDisplayFields($this->getShortFields());
    }

    /**
     * Get for activity feed display
     *
     * @return array
     */
    public function getFeedDisplay()
    {
        return $this->displayShortFields()->toArray();
    }

    /**
     * Map raw data to resource property
     *
     * @param string $state
     *
     * @throws UndefinedResourceName
     */
    protected function autoMapToProperties($state = "")
    {
        $mayId = $this->guessResourceId();

        foreach ((array)$this->rawData as $key => $value) {
            if ($key === $mayId || $key === "id" || $this->getIdFieldName() === $key) {
                $this->__setProperty('id', $value);
            } else {
                switch ($key) {
                    case "time_stamp":
                    case "timestamp":
                        $this->creation_date = $this->convertDatetime($value);
                        break;
                    case 'update_time':
                    case 'time_update':
                        $this->modification_date = $this->convertDatetime($value);
                        break;
                }
                if (property_exists(static::class, $key)) {
                    $this->__setProperty($key, $value);
                }
            }
        }

        // Map Owner
        if (!empty($this->rawData[self::NAMING_RESOURCE_OWNER_ID])
            && property_exists(static::class, self::NAMING_RESOURCE_OWNER)
            && !($this instanceof UserResource)) {


            $this->__setProperty(self::NAMING_RESOURCE_OWNER,
                UserResource::populate(UserResource::filterData($this->rawData)));
        }

        if (property_exists(static::class, self::NAMING_PROPERTY_STATISTIC)) {
            $this->__setProperty(self::NAMING_PROPERTY_STATISTIC, Statistic::fromArray($this->rawData));
        }
        if (property_exists(static::class, self::NAMING_PROPERTY_COORDINATE)) {
            $this->__setProperty(self::NAMING_PROPERTY_COORDINATE, Coordinate::fromArray($this->rawData, $this->getCoordinateMapping()));
        }

        if (!empty($state)) {
            $this->state = $state;
        }
    }

    protected function convertDatetime($value)
    {
        if ($value == 0) {
            return null;
        }
        return date('c', $value);
    }

    /**
     * @return $this|null
     * @throws \Exception
     */
    public function loadResource()
    {
        if ($this->state == self::STATE_POPULATED) {
            return null;
        }
        $resourceData = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->loadResourceById($this->id);

        $this->rawData = $resourceData;
        $this->autoMapToProperties(self::STATE_POPULATED);

        return $this;
    }

    /**
     * @return AbstractResourceApi|ResourceInterface|mixed
     * @throws \Exception
     */
    public function getApiService()
    {
        return NameResource::instance()->getApiServiceByResourceName($this->resource_name);
    }

    /**
     * @param array $resources list of resource
     *
     * @return $this
     * @throws \Exception
     */
    public function lazyLoad($resources)
    {
        foreach ($resources as $res) {
            $resource = $this->$res;
            if (is_array($resource)) {
                foreach ($resource as $obj) {
                    if (is_subclass_of($obj, ResourceBase::class)) {
                        $obj->loadResource();
                    }
                }
            } else {

                if ($resource instanceof ResourceBase) {
                    $resource->loadResource();
                }
            }
        }
        return $this;
    }

    /**
     * Define Metadata for Resource's fields
     *
     * Override this function in each sub class and adding metadata schema for each field
     *
     * @param ResourceMetadata|null $metadata
     */
    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        if ($metadata != null) {
            $this->metadata = $metadata;
        }
        if ($this->metadata == null) {
            $this->metadata = new ResourceMetadata();
        }

        // Default adding ID field
        $this->metadata
            ->mapField("id", ['type' => ResourceMetadata::INTEGER])
            ->mapField('privacy', ['type' => ResourceMetadata::INTEGER]);
    }

    private function __convertPropertyToMethodName($name, $preFix)
    {
        return $preFix . str_replace(" ", "", ucwords(str_replace("_", " ", $name)));
    }

    /**
     * Automatic call when assigning rawData into the resource
     *
     * @param string $property
     * @param mixed  $value
     */
    protected function __setProperty($property, $value)
    {
        if ($value instanceof ResourceBase) {
            $this->$property = $value;
        } else {
            // Convert value before setting field
            $value = $this->metadata->convert($property, $value);

            // Auto Mapping raw data into resource field
            $setter = $this->__convertPropertyToMethodName($property, "set");
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                $this->$property = $value;
            }
        }

    }

    /**
     * Get property values
     *
     * @param $property
     *
     * @return mixed
     */
    protected function __getProperty($property)
    {
        $getter = $this->__convertPropertyToMethodName($property, "get");

        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else if (property_exists($this, $property)) {
            return $this->$property;
        } else {
            return null;
        }
    }

    /**
     * @return string id field name stored in database
     */
    protected function guessResourceId()
    {
        if (!empty($this->getIdFieldName())) {
            return $this->getIdFieldName();
        }
        return str_replace("-", "_", $this->resource_name) . "_id";
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set extra field for the resource
     *
     * @param mixed $extra
     *
     * @return $this
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * Get Feed parameters. Use for loading Feed, comment and likes.
     * This method take @this->resource_name as like_type_id & comment_type_id
     * Override this function for override default behavior
     *
     * @return FeedParam|array
     */
    public function getFeedParam()
    {
        if (!$this->feed_param) {
            $this->feed_param = new FeedParam();
            $this->feed_param->setCommentTypeId($this->getCommentTypeId())
                ->setItemId($this->id)
                ->setFeedLink($this->getLink())
                ->setFeedTitle($this->getTitle())
                ->setLikeTypeId($this->getLikeTypeId())
                ->setReportModule($this->getCommentTypeId())
                ->setFeedIsLiked(isset($this->rawData['is_liked']) && $this->rawData['is_liked'] ? true : false)
                ->setFeedIsFriend(isset($this->rawData['is_friend']) && $this->rawData['is_friend'] ? true : false);

            if (isset($this->statistic) && $this->statistic instanceof Statistic) {
                $this->feed_param->setTotalComment($this->statistic->total_comment)
                    ->setTotalLike($this->statistic->total_like);
            }
        }
        return $this->feed_param->toArray();
    }


    /**
     * Override this function to override like_type_id
     *
     * @return string
     */
    public function getLikeTypeId()
    {
        return isset($this->like_type_id) ? $this->like_type_id : str_replace("-", "_", $this->resource_name);
    }

    /**
     * Override this function to override comment_type_id
     *
     * @return string
     */
    public function getCommentTypeId()
    {
        return isset($this->comment_type_id) ? $this->comment_type_id : ($this->resource_name != 'feed' ? str_replace("-", "_", $this->resource_name) : null);
    }

    /**
     * Set Feed parameter
     *
     * @param FeedParam $feed_param
     */
    public function setFeedParam($feed_param)
    {
        $this->feed_param = $feed_param;
    }

    /**
     * Override this function for override default behavior
     *
     * @return string
     */
    public function getTitle()
    {
        $title = '';
        if (isset($this->title)) {
            $title = $this->title;
        } else {
            if (isset($this->rawData['title'])) {
                $title = $this->rawData['title'];
            } else if (isset($this->rawData['name'])) {
                $title = $this->rawData['name'];
            }
        }
        if ($title) {
            return $this->parse->cleanOutput($title);
        }
        return '';
    }

    /**
     * Override this function for override default behavior
     *
     * @return string
     */
    public function getName()
    {
        $name = '';
        if (isset($this->name)) {
            $name = $this->name;
        } else if (isset($this->rawData['name'])) {
            $name = $this->rawData['name'];
        } else {
            if (isset($this->rawData['title'])) {
                $name = $this->rawData['title'];
            }
        }
        if ($name) {
            return $this->parse->cleanOutput($name);
        }
        return '';
    }

    /**
     * Set load feed_param when generate Resource to Array
     *
     * @param bool $enable
     *
     * @return $this
     */
    public function loadFeedParam($enable = true)
    {
        $this->load_feed_param = $enable;
        return $this;
    }

    public function getLink()
    {
        return null;
    }

    /**
     * This function has lower priority of toArray with field list parameter
     * It useful to control output of listing resource
     *
     * @param array $displayFields
     *
     * @return ResourceBase
     */
    public function setDisplayFields($displayFields)
    {
        $this->displayFields = $displayFields;
        return $this;
    }

    /**
     * Override this function to match with ID field name in database
     *
     * @return mixed
     */
    public function getIdFieldName()
    {
        return $this->idFieldName;
    }

    /**
     * @return string
     */
    public function getViewMode()
    {
        return $this->viewMode;
    }

    /**
     * @param string $viewMode
     *
     * @return $this
     */
    public function setViewMode($viewMode)
    {
        $this->viewMode = $viewMode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isListView()
    {
        return ($this->viewMode == self::VIEW_LIST);
    }

    /**
     * @return bool
     */
    public function isDetailView()
    {
        return ($this->viewMode == self::VIEW_DETAIL);
    }

    public function isDefaultView()
    {
        return ($this->viewMode == self::VIEW_DEFAULT);
    }

    /**
     * Get resource author
     *
     * @return UserResource|UserInterface
     */
    public function getAuthor()
    {
        return $this->__getProperty(self::NAMING_RESOURCE_OWNER);
    }

    /**
     * @return int return Object's Privacy
     */
    public function getAccessPrivacy()
    {
        return $this->__getProperty('privacy');
    }

    /**
     * Get resource identify
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return HyperLink[]
     */
    public function getSelf()
    {
        // return $this->self;
        // Current disable
        return null;

    }

    /**
     * @param HyperLink[] $self
     *
     * @return ResourceBase
     */
    public function setSelf($self)
    {
        foreach ($self as $key => $value) {
            if ($value === null) {
                unset($self[$key]);
            }
        }
        $this->self = $self;
        return $this;
    }

    /**
     * @return HyperLink[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param HyperLink[] $links
     *
     * @return ResourceBase
     */
    public function setLinks($links)
    {
        foreach ($links as $key => $value) {
            if ($value === null) {
                unset($links[$key]);
            }
        }
        $this->links = $links;
        return $this;
    }

    /**
     * @return AccessControl
     */
    public function getAccessControl()
    {
        return $this->accessControl;
    }

    /**
     * @param AccessControl $accessControl
     *
     * @return $this
     */
    public function setAccessControl($accessControl)
    {
        $this->accessControl = $accessControl;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return str_replace("-", "_", $this->resource_name);
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        if (defined('PHPFOX_REQUEST_FEED_ITEM_TYPE') && PHPFOX_REQUEST_FEED_ITEM_TYPE) {
            return PHPFOX_REQUEST_FEED_ITEM_TYPE;
        }
        return $this->module_name;
    }

    /**
     * @return string
     */
    public function getNoItemsMessage()
    {
        return 'No Item Founds';
    }

    /**
     * @param string|null $baseUrl
     *
     * @return array
     */
    public function getApiUrls($baseUrl = null)
    {
        $resourceName = $this->getResourceName();

        if (!$baseUrl) {
            $baseUrl = 'mobile/' . str_replace('_', '-', $resourceName);
        }

        return [
            'listing' => $baseUrl,
            'view'    => $baseUrl . '/:id',
            'formadd' => $baseUrl . '/form',
            'edit'    => $baseUrl . '/form/:id',
            'delete'  => $baseUrl . '/:id',
        ];
    }

    /**
     * @param mixed $module_name
     */
    public function setModuleName($module_name)
    {
        $this->module_name = $module_name;
    }


    /**
     * @param $params
     *
     * @return SettingParametersBag
     */
    public static function createSettingForResource($params)
    {
        if (!self::enableChat()) {
            foreach (['action_menu', 'membership_menu'] as $menu) {
                if (isset($params[$menu])) {
                    if (($key = array_search(Screen::ACTION_CHAT_WITH, array_column($params[$menu], 'value'))) !== false) {
                        unset($params[$menu][$key]);
                        $params[$menu] = array_values($params[$menu]);
                    }
                }
            }
        }
        return SettingParametersBag::createForResource($params);
    }

    /**
     * @param array $params
     *
     * @return SettingParametersBag
     */
    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName()
        ]);
    }

    /**
     * @return LocalizationInterface|object
     */
    protected function getLocalization()
    {
        if (!$this->localization) {
            $this->localization = Phpfox::getService(LocalizationInterface::class);
        }
        return $this->localization;
    }

    public function getDefaultImage($isCover = false, $resource = null)
    {
        $basePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/default-images/';

        return $basePath . ($resource ? $resource : $this->resource_name) . '/' . ($isCover ? 'no_image_cover.png' : 'no_image.png');
    }

    public function getUrlMapping($url, $queryArray)
    {
        return $url;
    }

    public function getIsPending()
    {
        return false;
    }

    public function getPrivacy()
    {
        return $this->privacy;
    }

    public function getAppImage($imageName = 'no-item')
    {
        $basePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/app-images/';

        return $basePath . $imageName . '.png';
    }

    public static function enableChat()
    {
        return (Phpfox::isApps('P_Rocketchat') && (setting('p_rocketchat_server'))) || Phpfox::isAppActive('PHPfox_IM');
    }

    /**
     * @return mixed
     */
    public function getCoordinateMapping()
    {
        return $this->coordinateMapping;
    }

    /**
     * @param mixed $coordinateMapping
     */
    public function setCoordinateMapping($coordinateMapping)
    {
        $this->coordinateMapping = $coordinateMapping;
    }

    /**
     * Get user raw data.
     *
     * @return array
     */
    public function getRawData() {
        return $this->rawData;
    }

}