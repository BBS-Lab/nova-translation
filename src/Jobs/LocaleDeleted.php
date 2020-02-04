<?php

namespace BBSLab\NovaTranslation\Jobs;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LocaleDeleted implements ShouldQueue
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
        Translation::query()
            ->where('locale_id', '=', $this->locale->getKey())
            ->cursor()
            ->each(function (Translation $translation) {
                $this->deleteTranslation($translation);
            });
    }

    /**
     * @param  \BBSLab\NovaTranslation\Models\Translation  $translation
     * @throws \Exception
     */
    protected function deleteTranslation(Translation $translation)
    {
        if (! empty($translation->translatable)) {
            $translation->translatable->delete();
        }

        Translation::query()
            ->where('translation_id', '=', $translation->translation_id)
            ->where('translatable_id', '=', $translation->translatable_id)
            ->where('translatable_type', '=', $translation->translatable_type)
            ->where('locale_id', '=', $translation->locale_id)
            ->delete();
    }
}
