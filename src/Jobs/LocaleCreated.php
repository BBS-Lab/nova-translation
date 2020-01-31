<?php

namespace BBSLab\NovaTranslation\Jobs;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use BBSLab\NovaTranslation\NovaTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LocaleCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \BBSLab\NovaTranslation\Models\Locale */
    protected $locale;

    /**
     * Create a new job instance.
     *
     * @param  \BBSLab\NovaTranslation\Models\Locale  $locale
     */
    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($models = NovaTranslation::translatableModels())) {
            return;
        }

        Translation::query()
            ->select('translation_id', 'translatable_type', 'MIN(translatable_id) as translatable_id')
            ->groupBy(['translatable_id', 'translatable_type'])
            ->orderBy('translation_id')
            ->cursor()
            ->each(function (Translation $translation) {
                $this->translateToNewLocale($translation);
            });
    }

    public function translateToNewLocale(Translation $translation)
    {
        $translation->translatable::withoutEvents(function () use ($translation) {
            /** @var \BBSLab\NovaTranslation\Models\Contracts\IsTranslatable $model */
            $model = $translation->translatable->query()->create(
                $translation->translatable->only(
                    $translation->translatable->getOnCreateTranslatable()
                )
            );
            $model->upsertTranslationEntry($this->locale->getKey(), $translation->translation_id);
        });
    }
}
