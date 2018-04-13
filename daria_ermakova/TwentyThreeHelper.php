<?php

namespace TwentyThree;

use Illuminate\Database\Eloquent\Collection;
use Common;
use Repositories\TwentyThreeConfigRepository;

/**
 * actions with twenty three table depends on we are on hq or on web
 * Class TwentyThreeHelper
 * @package TwentyThree
 */
class TwentyThreeHelper
{

    /**
     * @param $shopId
     * @return array
     */
    public static function getCredentialsByShop($shopId)
    {
        if(Common::isHQ()) {
            $result = \TwentyThreeConfigs::model()->find('shop_id = ' . $shopId);
        } else {
            $rep = new TwentyThreeConfigRepository();
            $result = $rep->find($shopId);
        }
        return !empty($result) ? $result->getAttributes() : [];
    }

    /**
     * @return mixed|array|Collection
     */
    public static function getCredentialsList()
    {
        if(Common::isHQ()) {
            $result = \TwentyThreeConfigs::model()->findAll();
        } else {
            $rep = new TwentyThreeConfigRepository();
            $result = $rep->all();
        }

        foreach ($result as $key => $item) {
            $result[$key] = $item->getAttributes();
        }
        return $result;
    }

    /**
     * @param $shopId
     * @param $credentials
     */
    public static function updateShopCredentials($shopId, $credentials)
    {
        if(Common::isHQ()) {
            $config = \TwentyThreeConfigs::model()->find('shop_id = ' . $shopId);
            if (!empty($config)) {
                $config->saveAttributes($credentials);
            }

        } else {
            $rep = new TwentyThreeConfigRepository();
            $rep->update($credentials, $shopId, 'shop_id');
        }
    }

    /**
     * find video on all domains from DB and return its data and which domain has this video
     * @param $videoId
     * @return array
     */
    public static function findVideoOnAllDomains($videoId)
    {
        $result = [];
        foreach (self::getCredentialsList() as $credentials) {
            $tt = new TwentyThreeClient($credentials['shop_id']);
            $videoData = $tt->getVideoById($videoId);
            if (!empty($videoData['data'])) {
                $result = $videoData['data'];
                $result['videos_domain'] = $credentials['videos_domain'];
                $result['shop_id'] = $credentials['shop_id'];
                break;
            }
        }
        return $result;
    }
}