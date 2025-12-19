<?php

namespace App\Service;

class PassStrCheck
{
    public function check(string $password): array
    {
        $length  = strlen($password) >= 8;
        $lower   = preg_match('/[a-z]/', $password) === 1;
        $upper   = preg_match('/[A-Z]/', $password) === 1;
        $number  = preg_match('/[0-9]/', $password) === 1;
        $special = preg_match('/[^a-zA-Z0-9]/', $password) === 1;

        $score = array_sum([(int)$length, (int)$lower, (int)$upper, (int)$number, (int)$special]);

        $strength = $score === 5 ? 'Strong' : ($score >= 3 ? 'Moderate' : 'Weak');

        return [
            'valid' => $score >= 3,
            'strength' => $strength,
            'score' => $score,
            'checks' => compact('length', 'lower', 'upper', 'number', 'special'),
        ];
    }
}
