<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use fund\Dto\FundDto;
use fund\Enum\CostPeriodIdEnum;
use fund\Helper\DbHelper;
use fund\Service\FundService;

CModule::IncludeModule("iblock");

$arSelect = ["ID", "IBLOCK_ID", "PROPERTY_CODE"];
$arFilter = ["IBLOCK_ID" => 10, "ACTIVE"=> "Y"];
$res = CIBlockElement::GetList(Array(), $arFilter, false, [], $arSelect);
$fundCodeAll = [];
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    if (true === isset($arFields['PROPERTY_CODE_VALUE'])) {
        $fundCodeAll[$arFields['PROPERTY_CODE_VALUE']] = $arFields['PROPERTY_CODE_VALUE'];
    }
}
$dbHelper = new DbHelper('capitalig');
$fundService = new FundService($dbHelper);

/**
 * @var FundDto[] $fundDtos
 */
$fundDtos = $fundService->getList($fundCodeAll);

/**
 * DateTimeImmutable[] $dateListByKey
 */
$dateListByKey = $fundService->getDatesForCost();
echo '<pre>';
foreach ($fundDtos as $fundDto) {
    $costs = $structures = [];
    $datePercentStart = null;
    foreach ($fundDto->getCosts() as $periodId => $costDto) {
        if (CostPeriodIdEnum::LAST_WORK_PERIOD === $periodId) {
            $datePercentStart = $costDto->getDate()->format('Y-m-d');
        }
        $costs[$periodId] = [
                'shareCost' => $costDto->getShareCost(),
                'assetCost' => $costDto->getAccetsCost(),
                'percent' => $costDto->getPercent(),
                'date' => $costDto->getDate() ? $costDto->getDate()->format('Y-m-d') : null
        ];
    }

    foreach ($fundDto->getStructures() as $structureDto) {
        $structures[] = [
            'value' => $structureDto->getValue(),
            'sumValue' => $structureDto->getSumValue(),
            'type' => $structureDto->getTypeId(),
            'percent' => $structureDto->getPercent()
        ];
    }
    var_dump([
            'id' => $fundDto->getFundId(),
            'name' => $fundDto->getName(),
            'desc' => $fundDto->getDescription(),
            'dateStartForCalcPercent' => $datePercentStart,
            'costs' => $costs,
            'structures' => $structures,
        ]
    );
}


foreach ($fundDtos as $fundDto) {
    $shareCosts = $assetCosts = [];
    $costDtos = $fundService->getCostByFundIdAndPeriod($fundDto->getFundId(), new DateTimeImmutable('-3 month'), new DateTimeImmutable());

    foreach ($costDtos as $costDto) {
        $shareCosts[$costDto->getDate()->format('Y-m-d')] = $costDto->getShareCost();
        $assetCosts[$costDto->getDate()->format('Y-m-d')] = $costDto->getAccetsCost();
    }
    var_dump([
        'fundName' => $fundDto->getName(),
        'shareCosts' => $shareCosts,
        'assetCosts' => $assetCosts,
    ]);
}
