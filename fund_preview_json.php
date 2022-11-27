<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

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

$fundDtos = $fundService->getList($fundCodeAll);

$dateFrom = new DateTimeImmutable(CostPeriodIdEnum::ALL[CostPeriodIdEnum::LAST_5_YEAR]);
$dateTill = new DateTimeImmutable();

$costDtos = [];
foreach ($fundDtos as $fundDto) {
    $costDtos[$fundDto->getFundId()] = $fundService->getCostByFundIdAndPeriod($fundDto->getFundId(), $dateFrom, $dateTill);
}

header("Content-Type: application/json");
echo json_encode([
    'data' => [
        'fund' => $fundDtos,
        'costs' => $costDtos
    ],
    'meta' => [
        'dataType' => $fundService->getDatesForCost(),
        'period' => [
            'dateFrom' => $dateFrom,
            'dateTill' => $dateTill,
        ],
    ],
]);