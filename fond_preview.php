<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use fund\Dto\FundDto;
use fund\Enum\CostColumnEnum;
use fund\Enum\CostPeriodIdEnum;
use fund\Helper\DateHelper;
use fund\Helper\DbHelper;
use fund\Service\FundService;

$fundService = (new FundService(new DbHelper('capitalig')));
$dateHelper = new DateHelper();
/**
 * @var FundDto[] $fundDtos
 */
$fundDtos = $fundService->getList([414, 411, 416, 417, 415, 406, 407, 408, 825, 826, 1000]);

/**
 * DateTimeImmutable[] $dateListByKey
 */
$dateListByKey = $fundService->getDatesForCost();
echo '<pre>';
foreach ($fundDtos as $fundDto) {
    $costs = [];
    $datePercentStart = null;
    foreach ($fundDto->getCosts() as $periodId => $costDto) {
        if (CostPeriodIdEnum::LAST_WORK_PERIOD === $periodId) {
            $datePercentStart = $costDto->getDate()->format('Y-m-d');
        }
        $costs[$costDto->getDate()->format('Y-m-d')] = [
                'shareCost' => $costDto->getShareCost(),
                'assetCost' => $costDto->getAccetsCost(),
                'percent' => $costDto->getPercent()
        ];
    }
    var_dump([
            'id' => $fundDto->getFundId(),
            'name' => $fundDto->getName(),
            'desc' => $fundDto->getDescription(),
            'dateStartForCalcPercent' => $datePercentStart,
            'costs' => $costs,
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
