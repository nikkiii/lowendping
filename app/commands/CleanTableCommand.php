<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CleanTableCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lowendping:archive';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Clean unsent responses older than 30 minutes, since the client likely exited the page.
		$unsent_min = \Carbon\Carbon::now()->subMinutes(30);
		
		QueryResponse::unsent()->where('created_at', '<', $unsent_min)->delete();
		
		// Check the archive
		if (Config::get('lowendping.archive.enabled', false)) {
			$mintime = \Carbon\Carbon::now()->subDays(Config::get('lowendping.archive.days'));
			
			$queries = Query::where('created_at', '<', $mintime)->get();
			foreach ($queries as $query) {
				$this->info('Deleting query ' . $query->id);
				QueryResponse::where('query_id', $query->id)->delete();
				$query->delete();
			}
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

}
