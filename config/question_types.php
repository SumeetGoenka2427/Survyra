<?php

use App\QuestionTypes\CesQuestionType;
use App\QuestionTypes\CheckboxQuestionType;
use App\QuestionTypes\CsatQuestionType;
use App\QuestionTypes\DateQuestionType;
use App\QuestionTypes\DropdownQuestionType;
use App\QuestionTypes\EmailQuestionType;
use App\QuestionTypes\EmojiRatingQuestionType;
use App\QuestionTypes\MatrixQuestionType;
use App\QuestionTypes\NpsQuestionType;
use App\QuestionTypes\NumberQuestionType;
use App\QuestionTypes\PhoneQuestionType;
use App\QuestionTypes\RadioQuestionType;
use App\QuestionTypes\RankingQuestionType;
use App\QuestionTypes\RatingStarsQuestionType;
use App\QuestionTypes\SliderQuestionType;
use App\QuestionTypes\TextareaQuestionType;
use App\QuestionTypes\TextboxQuestionType;
use App\QuestionTypes\YesNoQuestionType;
use App\QuestionTypes\FileUploadQuestionType;
use App\QuestionTypes\ImageChoiceQuestionType;

/*
|--------------------------------------------------------------------------
| Question Type Registry
|--------------------------------------------------------------------------
|
| Maps a question_types.key to the class implementing QuestionTypeContract.
| Adding a new question type is: one class implementing the contract, one
| line here, one row in the question_types table - nothing else changes.
|
*/

return [
    'nps' => NpsQuestionType::class,
    'csat' => CsatQuestionType::class,
    'ces' => CesQuestionType::class,
    'radio' => RadioQuestionType::class,
    'checkbox' => CheckboxQuestionType::class,
    'dropdown' => DropdownQuestionType::class,
    'textbox' => TextboxQuestionType::class,
    'textarea' => TextareaQuestionType::class,
    'number' => NumberQuestionType::class,
    'email' => EmailQuestionType::class,
    'phone' => PhoneQuestionType::class,
    'date' => DateQuestionType::class,
    'rating_stars' => RatingStarsQuestionType::class,
    'emoji_rating' => EmojiRatingQuestionType::class,
    'yes_no' => YesNoQuestionType::class,
    'matrix' => MatrixQuestionType::class,
    'ranking' => RankingQuestionType::class,
    'slider' => SliderQuestionType::class,
    'file_upload' => FileUploadQuestionType::class,
    'image_choice' => ImageChoiceQuestionType::class,
];
