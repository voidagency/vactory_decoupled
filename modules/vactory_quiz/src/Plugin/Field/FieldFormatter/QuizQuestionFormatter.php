<?php

namespace Drupal\vactory_quiz\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Quiz Question field formatter.
 *
 * @FieldFormatter(
 *   id = "vactory_quiz_question_formatter",
 *   label = @Translation("Quiz Question Default"),
 *   field_types = {
 *     "vactory_quiz_question"
 *   }
 * )
 */
class QuizQuestionFormatter extends FormatterBase {

  /**
   * Builds a renderable array for a field value.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field values to be rendered.
   * @param string $langcode
   *   The language that should be used to render the field.
   *
   * @return array
   *   A renderable array for $items, as an array of child elements keyed by
   *   consecutive numeric indexes starting from 0.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $answers = json_decode($item->question_answers, TRUE);
      $element[$delta] = [
        '#theme' => 'vactory_quiz__quiz_question',
        '#question' => [
          'value' => $item->question_text_value,
          'number' => $item->question_number,
          'answers' => $answers,
        ],
      ];
    }

    return $element;
  }

}
