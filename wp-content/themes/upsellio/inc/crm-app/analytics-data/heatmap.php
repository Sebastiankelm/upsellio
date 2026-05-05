<?php
if (!defined("ABSPATH")) {
    exit;
}

function upsellio_analytics_heatmap(): array
{
    return (array) get_option("ups_analytics_leads_heatmap", []);
}
