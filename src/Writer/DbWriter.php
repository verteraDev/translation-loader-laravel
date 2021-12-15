<?php

declare(strict_types=1);

namespace VerteraDev\TranslationLoader\Laravel\Writer;

use VerteraDev\TranslationLoader\Data\TranslationGroup;
use VerteraDev\TranslationLoader\TranslationManager;
use VerteraDev\TranslationLoader\Writer\TranslationWriterAbstract;

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
class DbWriter extends TranslationWriterAbstract
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

    public function write(TranslationGroup $translationGroup): bool
    {
        $activeLanguages = $this->manager->getLanguages();

        $languageLine = $this->getLanguageLineOrCreate($translationGroup);

        $text = [];
        if ($languageLine->text && is_array($languageLine->text)) {
            $text = $languageLine->text;
        }

        foreach ($translationGroup->items as $translationItem) {
            if (!in_array($translationItem->language, $activeLanguages)) {
                continue;
            }
            $text[$translationItem->language] = $translationItem->content;
        }

        $languageLine->text = $text;
        if ($languageLine->save()) {
            $languageLine->flushGroupCache();
            return true;
        }
        return false;
    }

    public function finalize(): void
    {
        // TODO: Implement finalize() method.
    }

    protected function getLanguageLineOrCreate(TranslationGroup $translationGroup): mixed
    {
        $modelClass = $this->modelClass;

        $languageLine = $modelClass::query()
            ->where('group', '=', $translationGroup->category)
            ->where('key', '=', $translationGroup->code)
            ->first();

        if (!$languageLine) {
            $languageLine = new $modelClass();
            $languageLine->group = $translationGroup->category;
            $languageLine->key = $translationGroup->code;
        }

        return $languageLine;
    }
}
