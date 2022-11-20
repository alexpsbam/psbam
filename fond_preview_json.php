<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use fund\Dto\FundDto;
use fund\Enum\CostPeriodIdEnum;
use fund\Helper\DateHelper;
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

$fundService = (new FundService(new DbHelper('capitalig')));
$dateHelper = new DateHelper();
/**
 * @var FundDto[] $fundDtos
 */
$fundDtos = $fundService->getList($fundCodeAll);

foreach ($fundDtos as $fundDto) {
    $costDtos[$fundDto->getFundId()] = $fundService->getCostByFundIdAndPeriod($fundDto->getFundId(), new DateTimeImmutable('-3 month'), new DateTimeImmutable());
}

header("Content-Type: application/json");
echo json_encode([
    'data' => [
        'fund' => $fundDtos,
        'costs' => $costDtos
    ],
    'meta' => [
        'dataTypeId' => CostPeriodIdEnum::ALL,
    ]
]);