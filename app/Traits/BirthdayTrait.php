<?php

namespace App\Traits;

/**
 * 誕生日用セレクトメニューなど
 */
trait BirthdayTrait
{
    /**
     * 誕生日(年)レンジを取得
     */
    public function getBirthDayYearRange(): array
    {
        return range(1911, date('Y'));
    }

    /**
     * selectメニュー用の誕生日(年)配列
     * 誕生日年（「YYYY => YYYY年」形式）
     *
     * @return array
     */
    public function getBirthDayYearSelect() : array
    {
        return array_combine(
            $this->getBirthdayYearRange(),
            array_map(
                function ($y) {
                    return "{$y}年";
                },
                $this->getBirthdayYearRange()
            )
        );
    }

    /**
     * selectメニュー用の誕生日(月)配列
     * 誕生日月（「MM => MM月」形式の）
     *
     * @return array
     */
    public function getBirthDayMonthSelect() : array
    {
        return array_combine(
            range(1, 12),
            array_map(
                function ($m) {
                    return "{$m}月";
                },
                range(1, 12)
            )
        );
    }

    /**
     * selectメニュー用の誕生日(日)配列
     * 誕生日日（「DD => DD日」形式の）
     *
     * @return array
     */
    public function getBirthDayDaySelect() : array
    {
        return array_combine(
            range(1, 31),
            array_map(
                function ($d) {
                    return "{$d}日";
                },
                range(1, 31)
            )
        );
    }

}
