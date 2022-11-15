<?php
declare(strict_types=1);

namespace fund\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\Connection;
use DateTimeImmutable;

class DbHelper
{
    private const CACHE_TIME = 60 * 60;

    private Connection $db;
    private Cache $cache;

    public function __construct(string $connectionName = 'default')
    {
        $this->db = Application::getConnection($connectionName);
        $this->cache = Cache::createInstance();
    }

    private function getFundBaseQuerySql(array $params = []): string
    {
        $selectSql = "
            SELECT 
                    fl.ID as id,
                    fl.UF_NAME as name,
                    fl.UF_FULLNAME as description,
                    fl.UF_CODE as code,
                    fl.UF_ACTIVE as active_status,
                    fl.UF_COSTS_AT as cost_date,
                    fl.UF_YIELDS_AT as yiealds_date
        ";
        $fromSql = "
            FROM 
                    fund_list as fl
        ";

        $whereSql = 'WHERE 1 = 1';
        foreach ($params as $name => $value) {
            if (is_integer($value)) {
                $whereSql .= "
                    AND {$name} = $value
                ";
            }
            if (is_string($value)) {
                $whereSql .= "
                    AND {$name} = $value
                ";
            }
            if (is_array($value)) {
                $values = implode(',', $value);
                $whereSql .= "
                    AND {$name} IN ({$values})
                ";
            }
        }

        return $selectSql . $fromSql . $whereSql;
    }

    public function getFundById(int $id): ?array
    {
        $cacheId = __METHOD__ . '#' . $id;
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }
        $baseSql = $this->getFundBaseQuerySql(
            [
                'fl.id' => $id,
                'fl.UF_STUCTURE_ON' => 1,
            ]
        );
        $data = $this->db->query($baseSql)->fetch();
        if (false === $data) {
            $data = null;
        }
        $this->setCache($cacheId, $data);
        return $data;
    }

    public function getFundList(): array
    {
        $cacheId =  __METHOD__;
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }
        $baseSql = $this->getFundBaseQuerySql(
            [
                'fl.UF_STUCTURE_ON' => 1,
                'fl.UF_CODE' => [
                    414, 411, 416, 417, 415, 406, 407, 408, 825, 826, 1000 // список нужных ПИФов, сейчас они выбираются исходя из UF_CODE в цикле по одном
                ]
            ]);

        $data = $this->db->query($baseSql)->fetchAll();

        $this->setCache($cacheId, $data);
        return $data;
    }

    private function hasCache(string $cacheId): bool
    {
        return $this->cache->initCache(self::CACHE_TIME, $cacheId);
    }

    private function getCache()
    {
        return $this->cache->getVars();
    }

    private function setCache(string $cacheId, $data): void
    {
        $this->cache->startDataCache(self::CACHE_TIME, $cacheId);
        $this->cache->endDataCache($data);
    }

    public function hasCostByDate(DateTimeImmutable $date): bool
    {
        $cacheId = __METHOD__ . '#' . $date->format('Ymd');
        if (true === $this->hasCache($cacheId)) {
            return (bool) $this->getCache();
        }
        $baseSql = "
            SELECT 1
            FROM fund_costs fc
            WHERE fc.UF_DATE = '{$date->format('Y-m-d')}'
            LIMIT 1
        ";
        $data = $this->db->query($baseSql)->fetch();
        $this->setCache($cacheId, (bool) $data);
        return (bool) $data;
    }

    public function findCostByFundIdAndDate(int $fundId, DateTimeImmutable $date): ?array
    {
        $cacheId = __METHOD__ . '#' . $date->format('Ymd') . '#' . $fundId;
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }
        $baseSql = "
            SELECT 
                fc.UF_DATE as date, 
                fc.UF_SHARE_COST as share_cost,
                fc.UF_ACCETS_COST as accets_cost
            FROM fund_costs fc
            WHERE fc.UF_DATE <= '{$date->format('Y-m-d')}'
                AND fc.UF_FUND_ID = {$fundId}
            ORDER BY fc.UF_DATE DESC
            LIMIT 1
        ";
        $data = $this->db->query($baseSql)->fetch();
        if (true === empty($data)) {
            $data = null;
        }
        $this->setCache($cacheId, $data);
        return $data;
    }
}