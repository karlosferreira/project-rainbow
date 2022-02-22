<?php
namespace Apps\Core_BetterAds\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v424
 * @package Apps\Core_BetterAds\Installation\Version
 */
class v424
{
    public function process()
    {
        $this->updateLanguages();
    }

    private function updateLanguages()
    {
        $aLanguages = Phpfox::getService('language')->getAll();

        if(!empty($aLanguages))
        {
            $sLanguageIds = implode(',', array_column($aLanguages, 'language_id'));

            if(db()->isField(':better_ads', 'languages'))
            {
                $aAds = db()->select('ads_id')
                    ->from(Phpfox::getT('better_ads'))
                    ->where('(languages IS NULL) OR (languages = "")')
                    ->execute('getSlaveRows');
                if(!empty($aAds))
                {
                    $sIds = implode(',',array_column($aAds,'ads_id'));
                    db()->update(Phpfox::getT('better_ads'), ['languages' => $sLanguageIds],'ads_id IN ('.$sIds.')');
                }

            }

            if(db()->isField(':better_ads_sponsor', 'languages'))
            {
                $aSponsors = db()->select('sponsor_id')
                    ->from(Phpfox::getT('better_ads_sponsor'))
                    ->where('(languages IS NULL) OR (languages = "")')
                    ->execute('getSlaveRows');
                if(!empty($aSponsors))
                {
                    $sIds = implode(',',array_column($aSponsors,'sponsor_id'));
                    db()->update(Phpfox::getT('better_ads_sponsor'), ['languages' => $sLanguageIds],'sponsor_id IN ('.$sIds.')');
                }

            }
        }

    }
}