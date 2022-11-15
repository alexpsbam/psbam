<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use fund\Dto\FundDto;
use fund\Enum\CostColumnEnum;
use fund\Enum\CostPeriodIdEnum;
use fund\Helper\DbHelper;
use fund\Service\FundService;

$fundService = (new FundService(new DbHelper('capitalig')));

/**
 * @var FundDto[] $fundDtos
 */
$fundDtos = $fundService->getList();

/**
 * DateTimeImmutable[] $dateListByKey
 */
$dateListByKey = $fundService->getDatesForCostByDateStart()
?>
<table>
    <tbody>
    <tr>
<?php
foreach (CostColumnEnum::LIST as $keyColumn => $text) {
?>
    <td>
    <?=$text?>
    <? if (
            true === in_array($keyColumn, [CostPeriodIdEnum::LAST_WORK_PERIOD, CostPeriodIdEnum::LAST_DAY])
            && true === isset($dateListByKey[$keyColumn])
    ) {
        echo $dateListByKey[$keyColumn]->format('Y-m-d');
    }
    foreach ($fundDtos as $fundDto) {
        if (CostColumnEnum::NAME_FUND === $keyColumn) {
            echo '<br>';
            echo $fundDto->getName() . '<br>';
            echo $fundDto->getDescription() . '<br>';
            echo '-----<br/>';
        }

        if (true === key_exists($keyColumn, CostPeriodIdEnum::PERIODS)) {
            echo '<br>';
            echo number_format($fundDto->getCosts()[$keyColumn]->getPercent(), 2, '.', ',') . '%<br>';
            echo '-----<br/>';
        }

        if (true === in_array($keyColumn, [CostPeriodIdEnum::LAST_WORK_PERIOD, CostPeriodIdEnum::LAST_DAY])) {
            echo '<br>';
            echo $fundDto->getCosts()[$keyColumn]->getShareCost() . '<br>';
            echo '-----<br/>';
        }
    }
    ?>

    </td>
<?php
}
?>
    </tbody>
</table>
