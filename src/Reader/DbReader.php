<?php

declare(strict_types=1);

namespace VerteraDev\TranslationLoader\Laravel\Reader;

use Generator;
use VerteraDev\TranslationLoader\Data\TranslationGroup;
use VerteraDev\TranslationLoader\Data\TranslationItem;
use VerteraDev\TranslationLoader\Reader\TranslationReaderAbstract;
use VerteraDev\TranslationLoader\TranslationManager;

/**
 * Пример данных $modelClass
 *  [
 *      'id' => 1,
 *      'group' => 'app',
 *      'key' => 'phrase1',
 *      'text' => [
 *          'en' => 'text in en',
 *          'ru' => 'text in ru
 *      ]
 *  ]
 */
class DbReader extends TranslationReaderAbstract
{
    /** @var string */
    public $modelClass = '\App\Models\LanguageLine';

    public function __construct(TranslationManager $manager, ?string $modelClass = null)
    {
        parent::__construct($manager);

        if ($modelClass) {
            $this->modelClass = $modelClass;
        }
    }

    public function read(): Generator
    {
        $modelClass = $this->modelClass;
        $activeLanguages = $this->manager->getLanguages();

        foreach ($modelClass::query()->cursor() as $languageLine) {
            $translationGroup = new TranslationGroup();
            $translationGroup->category = $languageLine->group;
            $translationGroup->code = $languageLine->key;

            $text = $languageLine->text;
            foreach ($activeLanguages as $language) {
                $translationItem = new TranslationItem();
                $translationItem->language = $language;
                if (isset($text[$language])) {
                    $translationItem->content = $text[$language];
                } else {
                    $translationItem->content = '';
                }
                $translationGroup->items[] = $translationItem;
            }

            yield $translationGroup;
        }
    }
}

