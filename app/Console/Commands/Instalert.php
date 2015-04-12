<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use MetzWeb\Instagram\Instagram;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Post;

class Instalert extends Command {

	const APP_KEY = 'a8813a12de174b009df721fb3889fa65';

	const JINXED_USER_ID = '32576604';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'instalert:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run the command to query Instagram and send notifications if new content found.';

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
		$instagram = new Instagram(self::APP_KEY);

		$results = $instagram->getUserMedia(self::JINXED_USER_ID);

		$posts = new Collection;

		foreach($results->data as $key => $result) {
			$post = new Post;

			$post->service_id = $result->id;
			$post->post_path = $result->link;
			$post->media_link = $result->images->standard_resolution->url;
			$post->type = $result->type;
			$post->poster_id = $result->user->id;
			$post->created_time = $result->created_time;
			$posts->add($post);
		}

		$newPosts = $posts->filter(function($posts) use ($post) {
			return is_null(Post::where('service_id', $post->service_id)->first());
		});

		foreach($newPosts as $post) {
			$post->save();

			$AccountSid = 'AC16d9735b5bc62d2da35645a57592a7ac';
			$AuthToken = 'fe83b2c93584ff131eb1cd7f9704b5a8';

			$numbers = array('330-730-9623', '609-410-8626');

			$post_time = Carbon::createFromTimeStamp($post->created_time)->toFormattedDateString();

			$body = sprintf("Hey hi hello. Just want to let you know that %s just uploaded a post on Instagram at %s. View it here: %s \n -- Dylan's Server", 'Jinxed', $post_time, $post->post_path);

			// Sending text
			$client = new \Services_Twilio($AccountSid, $AuthToken);

			foreach($numbers as $number) {
				$message = $client->account->messages->create(array(
				    "From" => "786-393-6488",
				    "To" => "330-730-9623",
				    "Body" => $body,
				));
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
		return [
			['example', InputArgument::OPTIONAL, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}