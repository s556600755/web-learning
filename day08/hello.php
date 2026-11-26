<?php
$shop = "府城小吃";
$hour = (int) date("H");

$greeting = match (true) {
    $hour < 11  => "早安",
    $hour < 17  => "午安",
    default     => "晚安",
};

echo "$greeting，歡迎光臨 $shop";