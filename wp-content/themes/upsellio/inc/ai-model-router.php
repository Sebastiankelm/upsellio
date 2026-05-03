<?php

if (!defined("ABSPATH")) {
    exit;
}

/**
 * Router modeli AI — zwraca właściwy model dla danego zadania.
 * Nadpisanie globalne: stała UPSELLIO_AI_MODEL_ALL lub filtry per-zadanie.
 *
 * Zadania: blog_post | offer_fill | inbox_draft | inbox_followup |
 *          lead_scoring | intent_classify | topic_generator |
 *          keyword_cluster | competitor_analysis | client_plan |
 *          suggestions | suggestions_clusters | blog_keyword_research | cpt_ai_optimize
 */
function upsellio_ai_model_for(string $task): string
{
    $task = sanitize_key($task);
    if ($task === "") {
        $task = "suggestions";
    }

    if (defined("UPSELLIO_AI_MODEL_ALL") && (string) UPSELLIO_AI_MODEL_ALL !== "") {
        $forced = (string) UPSELLIO_AI_MODEL_ALL;

        return function_exists("upsellio_anthropic_crm_normalize_model_id")
            ? upsellio_anthropic_crm_normalize_model_id($forced)
            : $forced;
    }

    $haiku = trim((string) get_option("ups_ai_model_haiku", "claude-haiku-4-5-20251001"));
    $sonnet = trim((string) get_option("ups_ai_model_sonnet", "claude-sonnet-4-5"));

    $global = trim((string) get_option("ups_anthropic_model", ""));
    if ($haiku === "") {
        $haiku = $global !== "" ? $global : "claude-haiku-4-5-20251001";
    }
    if ($sonnet === "") {
        $sonnet = $global !== "" ? $global : "claude-haiku-4-5-20251001";
    }

    $map = [
        "blog_post" => $sonnet,
        "offer_fill" => $sonnet,
        "inbox_draft" => $sonnet,
        "client_plan" => $sonnet,
        "inbox_followup" => $haiku,
        "lead_scoring" => $haiku,
        "intent_classify" => $haiku,
        "topic_generator" => $haiku,
        "keyword_cluster" => $haiku,
        "competitor_analysis" => $haiku,
        "suggestions" => $haiku,
        "blog_keyword_research" => $haiku,
        "suggestions_clusters" => $haiku,
        "cpt_ai_optimize" => $sonnet,
    ];

    $model = $map[$task] ?? $haiku;

    /**
     * Filtr ogólny: @param string $model @param string $task
     */
    $model = (string) apply_filters("upsellio_ai_model_for", $model, $task);

    /**
     * Filtr per zadanie, np. upsellio_ai_model_for_blog_post
     */
    $model = (string) apply_filters("upsellio_ai_model_for_{$task}", $model);

    return function_exists("upsellio_anthropic_crm_normalize_model_id")
        ? upsellio_anthropic_crm_normalize_model_id($model)
        : $model;
}
