<?php

namespace VKauto\Workers\FriendsWatcherWorker;

use VKauto\Interfaces\WorkerInterface;
use VKauto\Auth\Account;
use VKauto\Utils\QueryBuilder;
use VKauto\Utils\Request;
use VKauto\Utils\Log;

class FriendsWatcher implements WorkerInterface
{
	/**
	 * Состояние работы воркера
	 * @var boolean
	 */
	protected $workInProcess = false;

	/**
	 * Промежуток между запросами в секундах
	 * @var int
	 */
	public $seconds;

	/**
	 * Класс аккаунта, с которым работает воркер
	 * @var VKauto\Auth\Account
	 */
	public $account;

	public function __construct($minutes = 5, Account $account)
	{
		$this->seconds = $minutes * 60;
		$this->account = $account;
	}

	private function loop()
	{
		while ($this->workInProcess)
		{
			sleep($this->seconds);

			$followers = $this->getFollowers();

			if ($followers->count > 0)
			{
				foreach ($followers->items as $follower)
				{
					$response = $this->addFriend($follower);

					if ($response == 2)
					{
						Log::write("{$follower} was added to friends.", ['FriendsWatcher']);
					}
				}
			}
			else
			{
				Log::write('No new friend requests.', ['FriendsWatcher']);
			}
		}
	}

	private function getFollowers()
	{
		$response = Request::VK(QueryBuilder::buildURL('users.getFollowers', ['access_token' => $this->account->access_token, 'user_id' => $this->account->user_id, 'count' => 1000]), $this->account->captcha);

		return $response->response;
	}

	private function addFriend($id)
	{
		$response = Request::VK(QueryBuilder::buildURL('friends.add', ['access_token' => $this->account->access_token, 'user_id' => $id]), $this->account->captcha);

		return $response->response;
	}

	public function start()
	{
		$this->workInProcess = true;
		Log::write('Worker started.', ['FriendsWatcher']);
		$this->loop();
	}

	public function stop()
	{
		$this->workInProcess = false;
	}

	public static function needsAccountClass()
	{
		return true;
	}
}
