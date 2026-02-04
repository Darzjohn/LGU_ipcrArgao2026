define('VL_SL_MONTHLY', 1.25);
define('DAILY_RATE', 0.0416667);

function hoursToDay($h, $m) {
    return round((($h * 60) + $m) / 480, 3);
}

function computeMonthlyEarned($daysPresent, $lwop, $vlBalance) {

    // Table I
    if ($daysPresent >= 15 && $lwop == 0) {
        return VL_SL_MONTHLY;
    }

    // Table III
    if ($vlBalance == 0 && $lwop > 0) {
        return round($daysPresent * DAILY_RATE, 3);
    }

    return 0;
}
