<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Model('meta/llama3-70b-instruct')]
class TravelAdvisorAgent implements Agent
{
    use Promptable;

    public function __construct(
        protected string $mood,
        protected string $budget,
        protected string $duration,
        protected string $companion,
    ) {}

    public function instructions(): string
    {
        return 'You are an expert travel advisor for Smart Trip Planner. '
            . 'You MUST respond with a valid JSON object only — no markdown, no backticks, no explanation. '
            . 'The JSON must contain these exact keys: destination, description, estimated_cost, best_time_to_visit, top_activities, travel_tip.';
    }

    public function buildPrompt(): string
    {
        $durationLabel = match ($this->duration) {
            'weekend' => 'a weekend getaway (2-3 days)',
            'week'    => 'a one-week trip',
            'long'    => 'an extended trip (2+ weeks)',
            default   => $this->duration,
        };

        $budgetLabel = match ($this->budget) {
            'budget'  => 'budget-friendly (under $1,500)',
            'mid'     => 'mid-range ($1,500-$4,000)',
            'luxury'  => 'luxury (no strict budget)',
            default   => $this->budget,
        };

        $example = '{
  "destination": "City, Country",
  "description": "Why this destination is perfect for these preferences",
  "estimated_cost": "1,200 - 2,000 USD per person",
  "best_time_to_visit": "Month - Month",
  "top_activities": "Activity 1, Activity 2, Activity 3",
  "travel_tip": "One insider tip for this traveler type"
}';

        return "Recommend a travel destination for: mood={$this->mood}, budget={$budgetLabel}, duration={$durationLabel}, traveling as={$this->companion}.\n\nRespond ONLY with this JSON structure, no other text:\n{$example}";
    }
}