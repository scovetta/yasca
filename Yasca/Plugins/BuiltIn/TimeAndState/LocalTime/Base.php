<?
declare(encoding='UTF-8');
namespace Yasca\Plugins\BuiltIn\TimeAndState\LocalTime;

trait Base {
	use \Yasca\Plugins\BuiltIn\Base;

	protected function newResult(){
		return (new \Yasca\Result)->setOptions([
			'severity' => 5,
			'category' => 'Local Time',
			'description' => <<<'EOT'
Do not use local time values for comparison to each other, long term storage (database),
or cache expirations. Instead, use UTC time or an _offset() variant for your platform.

From PHP:
	It is not safe to rely on the system's timezone settings. You are *required* to use the
	date.timezone setting or the date_default_timezone_set() function. In case you used any of
	those methods and you are still getting this warning, you most likely misspelled the timezone identifier.
	We selected the timezone 'UTC' for now, but please set date.timezone to select your timezone.


Timezone changes on the server or daylight saving time events cause incorrect comparisons between times.
This is a larger threat than the system UTC time changing for Windows applications because changing the
time in Windows requires Administrative privileges, whereas changing the timezone does not.

If local time is merely displayed to the user, unexpected changes may only annoy users.
If local time is used for session or credential expiration, this creates an opportunity for other attacks.
If local time is used for throttling or caching, this creates an opportunity for denial of service events
EOT
,			'references' => [
				'https://cwe.mitre.org/data/definitions/613.html' =>
					'CWE-613: Insufficient Session Expiration',
				'https://cwe.mitre.org/data/definitions/361.html' =>
					'CWE-361: Time and State',
				'http://msdn.microsoft.com/en-us/library/system.web.caching.cache.insert.aspx' =>
					'MSDN: ASP.NET Web Cache Insert',
				'http://php.net/manual/en/function.date-default-timezone-set.php' =>
					'PHP: Set Timezone',
			],
		]);
	}
}