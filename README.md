# FriendsWatcherWorker
Проверяет каждые n минут заявки в друзья, после чего принимает их.

# Stand-alone использование
```php
use VKauto\Auth\Auth;
use VKauto\CaptchaRecognition\Captcha;
use VKauto\Workers\FriendsWatcherWorker\FriendsWatcher;

$account = Auth::directly('+79057151171', 'password');
$account->captcha = new Captcha(Captcha::AntiCaptchaService, 'API key');

$worker = new FriendsWatcher(5, $account);
$worker->start();
```
