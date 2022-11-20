<?php
declare(strict_types=1);

namespace fund\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\DB\Connection;
use DateTimeImmutable;
use fund\Enum\Db\CostFieldNameEnum;

class DbHelper
{
    private const CACHE_TIME = 60 * 60;
    private const SQL_WHERE = "WHERE 1 = 1";

    private Connection $db;
    private Cache $cache;

    public function __construct(string $connectionName = 'default')
    {
        $this->db = Application::getConnection($connectionName);
        $this->cache = Cache::createInstance();
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

    /**
     * @param int[] $ufCodeIds
     */
    public function getFundList(array $ufCodeIds): array
    {
        sort($ufCodeIds);

        $cacheId =  __METHOD__ . implode(',', $ufCodeIds);
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }

        $params = [];
        if (false === empty($ufCodeIds)) {
            $params['fl.UF_CODE'] = $ufCodeIds;
        }
        $baseSql = $this->getFundBaseQuerySql($params);
        $data = $this->db->query($baseSql)->fetchAll();

        $this->setCache($cacheId, $data);
        return $data;
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
        $data = $this->db->query($baseSql)->fetchRaw();
        $this->setCache($cacheId, (bool) $data);
        return (bool) $data;
    }

    public function getCostByFundIdAndDate(int $fundId, DateTimeImmutable $date): ?array
    {
        $cacheId = __METHOD__ . '#' . $date->format('Ymd') . '#' . $fundId;
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }

        $baseSql = $this->getCostBaseQuerySql([
            'fc.UF_FUND_ID' => $fundId,
            CostFieldNameEnum::TILL_DATE => $date,
        ]);
        $data = $this->db->query($baseSql, $limit = 1)->fetch();
        if (true === empty($data)) {
            $data = null;
        }
        $this->setCache($cacheId, $data);
        return $data;
    }

    public function getCostByFundIdAndPeriod(int $fundId, DateTimeImmutable $dateFrom, DateTimeImmutable $dateTill): array
    {
        $cacheId = __METHOD__ . '#'  . $fundId . '#' . $dateFrom->format('Ymd') . '#' . $dateTill->format('Ymd');
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }
        $baseSql = $this->getCostBaseQuerySql([
            'fc.UF_FUND_ID' => $fundId,
            CostFieldNameEnum::FROM_DATE => $dateFrom,
            CostFieldNameEnum::TILL_DATE => $dateTill,
        ]);

        $data = $this->db->query($baseSql)->fetchAll();
        $this->setCache($cacheId, $data);
        return $data;
    }

    public function getStructureByFundId(int $fundId): array
    {
        $cacheId = __METHOD__ . '#'  . $fundId;
        if (true === $this->hasCache($cacheId)) {
            return $this->getCache();
        }

        $baseSql = $this->getStructreBaseQuerySql([
            'fs.UF_FUND_ID' => $fundId,
            'fs.UF_TYPE_ID' => [1, 2],
        ]);

        $data = $this->db->query($baseSql)->fetchAll();
        $this->setCache($cacheId, $data);
        return $data;
    }

    private function getFundBaseQuerySql(array $values = []): string
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

        $whereSql = $this->createSqlWhere($values);

        return $selectSql . $fromSql . $whereSql;
    }

    private function getCostBaseQuerySql(array $values = []): string
    {
        $selectSql = "
            SELECT 
                fc.UF_DATE as date, 
                fc.UF_SHARE_COST as share_cost,
                fc.UF_ACCETS_COST as accets_cost
        ";
        $fromSql = "
            FROM 
                   fund_costs fc
        ";

        $whereSql = $this->createSqlWhere($values);

        $orderSql = "
            ORDER BY fc.UF_DATE DESC
        ";

        return $selectSql . $fromSql . $whereSql . $orderSql;
    }

    private function getStructreBaseQuerySql(array $values = []): string
    {
        $selectSql = "
            SELECT 
                fs.UF_FUND_ID as fund_id, 
                fs.UF_TITLE as title,
                fs.UF_TYPE_ID as type_id,
                (fs.UF_VALUE) as value
        ";
        $fromSql = "
            FROM 
                   fund_structure fs
        ";

        $whereSql = $this->createSqlWhere($values);

        $orderSql = "
            ORDER BY fs.UF_VALUE DESC
        ";

        return $selectSql . $fromSql . $whereSql . $orderSql;
    }

    private function createSqlWhere(array $values): string
    {
        $whereSql = self::SQL_WHERE;
        foreach ($values as $name => $value) {
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
                $whereSql .= " AND {$name} IN (";
                foreach ($value as $val) {
                    if (0 !== (int) $val) {
                        $whereSql .= ",{$val}";
                    } else {
                        $whereSql .= ",'{$val}'";
                    }
                }
                $whereSql .= ")";
                $whereSql = str_replace('(,', '(', $whereSql);
            }

            if ($value instanceof DateTimeImmutable) {
                if (CostFieldNameEnum::EQUAL_DATE === $name) {
                    $whereSql .= "
                        AND fc.UF_DATE = '{$value->format('Y-m-d')}'
                ";
                }
                if (CostFieldNameEnum::FROM_DATE === $name) {
                    $whereSql .= "
                        AND fc.UF_DATE >= '{$value->format('Y-m-d')}'
                ";
                }
                if (CostFieldNameEnum::TILL_DATE === $name) {
                    $whereSql .= "
                        AND fc.UF_DATE <= '{$value->format('Y-m-d')}'
                ";
                }
            }
        }
        return $whereSql;
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
}