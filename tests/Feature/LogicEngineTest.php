<?php

use App\Models\SurveyLogicRule;
use App\Services\LogicEngine;

function ruleWithConditions(array $conditions): SurveyLogicRule
{
    return new SurveyLogicRule(['conditions' => $conditions]);
}

test('equals operator matches case-insensitively', function () {
    $engine = new LogicEngine;
    $rule = ruleWithConditions([['question_id' => 1, 'operator' => 'equals', 'value' => 'Yes']]);

    expect($engine->evaluate($rule, [1 => 'yes']))->toBeTrue();
    expect($engine->evaluate($rule, [1 => 'no']))->toBeFalse();
});

test('not_equals operator', function () {
    $engine = new LogicEngine;
    $rule = ruleWithConditions([['question_id' => 1, 'operator' => 'not_equals', 'value' => 'no']]);

    expect($engine->evaluate($rule, [1 => 'yes']))->toBeTrue();
    expect($engine->evaluate($rule, [1 => 'no']))->toBeFalse();
});

test('contains operator', function () {
    $engine = new LogicEngine;
    $rule = ruleWithConditions([['question_id' => 1, 'operator' => 'contains', 'value' => 'refund']]);

    expect($engine->evaluate($rule, [1 => 'I want a refund please']))->toBeTrue();
    expect($engine->evaluate($rule, [1 => 'everything was great']))->toBeFalse();
});

test('greater_than and less_than operators', function () {
    $engine = new LogicEngine;
    $gt = ruleWithConditions([['question_id' => 1, 'operator' => 'greater_than', 'value' => 5]]);
    $lt = ruleWithConditions([['question_id' => 1, 'operator' => 'less_than', 'value' => 5]]);

    expect($engine->evaluate($gt, [1 => 8]))->toBeTrue();
    expect($engine->evaluate($gt, [1 => 3]))->toBeFalse();
    expect($engine->evaluate($lt, [1 => 3]))->toBeTrue();
    expect($engine->evaluate($lt, [1 => 8]))->toBeFalse();
});

test('is_empty and is_not_empty operators', function () {
    $engine = new LogicEngine;
    $empty = ruleWithConditions([['question_id' => 1, 'operator' => 'is_empty']]);
    $notEmpty = ruleWithConditions([['question_id' => 1, 'operator' => 'is_not_empty']]);

    expect($engine->evaluate($empty, [1 => '']))->toBeTrue();
    expect($engine->evaluate($empty, [1 => 'something']))->toBeFalse();
    expect($engine->evaluate($notEmpty, [1 => 'something']))->toBeTrue();
    expect($engine->evaluate($notEmpty, [1 => null]))->toBeFalse();
});

test('multiple conditions are ANDed together', function () {
    $engine = new LogicEngine;
    $rule = ruleWithConditions([
        ['question_id' => 1, 'operator' => 'equals', 'value' => 'no'],
        ['question_id' => 2, 'operator' => 'less_than', 'value' => 5],
    ]);

    expect($engine->evaluate($rule, [1 => 'no', 2 => 2]))->toBeTrue();
    expect($engine->evaluate($rule, [1 => 'no', 2 => 8]))->toBeFalse();
    expect($engine->evaluate($rule, [1 => 'yes', 2 => 2]))->toBeFalse();
});
