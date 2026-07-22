<?php

use App\Contracts\QuestionTypeContract;
use App\Services\QuestionTypeRegistry;

test('every configured question type resolves to a class implementing the contract', function () {
    $registry = app(QuestionTypeRegistry::class);

    $keys = array_keys(config('question_types'));

    expect($keys)->toHaveCount(18);

    foreach ($keys as $key) {
        expect($registry->resolve($key))->toBeInstanceOf(QuestionTypeContract::class);
    }
});

test('registry all() returns one instance per configured type', function () {
    $registry = app(QuestionTypeRegistry::class);

    expect($registry->all())->toHaveCount(count(config('question_types')));
});
