<?php

namespace App\Console\Commands;

use App\Enums\CompanyPersonType;
use App\Models\Company;
use App\Models\ExitStrategy;
use App\Models\Investor;
use App\Models\Person;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportUnicornGraduatesTechAvivCommand extends Command
{
    protected $signature = 'import:unicorn-graduates-tech-aviv';

    protected $description = 'Command description';

    public function handle(): void
    {
        $json = file_get_contents(storage_path('app/private/2-unicorn-graduates.json'));

        $allData = json_decode($json, true);

        $progressBar = $this->output->createProgressBar(count($allData));

        foreach ($allData as $data) {

            $dataFields = [
                'name' => str(data_get($data, 'Company'))->lower()->trim(),
                'exit_valuation' => data_get($data, 'Valuation at Exit'),
                'url' => data_get($data, 'Website'),
                'total_funding' => data_get($data, 'Total Funding'),
                'headquarter' => data_get($data, 'HQ'),
                'founded_at' => \Carbon\Carbon::createFromFormat('Y', data_get($data, 'Founded')),
                'exit_strategy_id' => ExitStrategy::query()
                    ->where('title', data_get($data, 'Exit'))
                    ->firstOrCreate(['title' => data_get($data, 'Exit')])->id,
                'stock_symbol' => data_get($data, 'Stock Symbol or Acquirer'),
                'description' => data_get($data, 'Description'),
                'last_funding_date' => data_get($data, 'Last Funding'),
                'stock_quote' => data_get($data, 'Stock Qoute'),
            ];

            $company = Company::whereRaw('LOWER(name) = ?', [
                str(data_get($data, 'Company'))->lower()->trim(),
            ])->firstOrCreate([
                'name' => data_get($data, 'Name'),
            ], $dataFields);

            if (! $company->wasRecentlyCreated) { // retrieved from database
                $company->update($dataFields);
            }

            //$company = Company::updateOrCreate(
            //    ['name' => \str(data_get($data, 'Company'))->lower()->value()],
            //    [
            //        'exit_valuation' => data_get($data, 'Valuation at Exit'),
            //        'url' => data_get($data, 'Website'),
            //        'total_funding' => data_get($data, 'Total Funding'),
            //        'headquarter' => data_get($data, 'HQ'),
            //        'founded_at' => \Carbon\Carbon::createFromFormat('Y', data_get($data, 'Founded')),
            //        'exit_strategy_id' => ExitStrategy::query()
            //            ->where('title', data_get($data, 'Exit'))
            //            ->firstOrCreate(['title' => data_get($data, 'Exit')])->id,
            //        'stock_symbol' => data_get($data, 'Stock Symbol or Acquirer'),
            //        'description' => data_get($data, 'Description'),
            //        'last_funding_date' => data_get($data, 'Last Funding'),
            //        'stock_quote' => data_get($data, 'Stock Qoute'),
            //    ]);

            $foundersString = data_get($data, 'Founders');
            $founders = \Str::of($foundersString)
                ->chopEnd('...')
                ->explode(',')
                ->reject(fn ($founder) => empty(trim($founder)));

            foreach ($founders as $founder) {
                $person = Person::firstOrCreate(
                    ['full_name' => trim($founder)],
                    ['job_title' => 'Founder '.$company->name]
                );

                if (empty($person->job_title)) {
                    $person->update(['job_title' => 'Founder '.$company->name]);
                }

                if ($company->people()->where('person_id', $person->id)->doesntExist()) {
                    $company->people()->attach($person, ['type' => CompanyPersonType::Founder]);
                }
            }

            $investorsString = data_get($data, 'Top Investors');
            $investors = Str::of($investorsString)
                ->explode(',')
                ->reject(fn ($investor) => empty(trim($investor)));

            foreach ($investors as $investor) {
                $investor = Investor::updateOrCreate([
                    'name' => trim($investor),
                ]);

                if ($company->investors()->where('investor_id', $investor->id)->doesntExist()) {
                    $company->investors()->attach($investor);
                }
            }

            $tagsString = data_get($data, 'Sectors');
            $tags = \Str::of($tagsString)
                ->explode(',')
                ->reject(fn ($investor) => empty(trim($investor)));

            $tagsIds = [];
            foreach ($tags as $tag) {
                $tag = Tag::updateOrCreate([
                    'name' => trim($tag),
                ]);

                $tagsIds[] = $tag->id;
            }
            $company->syncTags($tagsIds);

            $this->line("Processed importing: {$company->name}");
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\nProcessed Completed!");

    }
}