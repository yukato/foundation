<?php
namespace LaravelRocket\Foundation\Helpers\Production;

use Carbon\Carbon;
use LaravelRocket\Foundation\Helpers\DateTimeHelperInterface;

class DateTimeHelper implements DateTimeHelperInterface
{
    const PRESENTATION_TIME_ZONE_SESSION_KEY = 'presentation-time-zone';

    public function setPresentationTimeZone($timezone = null)
    {
        session()->put(static::PRESENTATION_TIME_ZONE_SESSION_KEY, $timezone);
    }

    public function clearPresentationTimeZone()
    {
        session()->remove(static::PRESENTATION_TIME_ZONE_SESSION_KEY);
    }

    public function dateTime($dateTimeStr, \DateTimeZone $timezoneFrom = null, \DateTimeZone $timezoneTo = null)
    {
        $timezoneFrom = empty($timezoneFrom) ? $this->timezoneForPresentation() : $timezoneFrom;
        $timezoneTo   = empty($timezoneTo) ? $this->timezoneForStorage() : $timezoneTo;

        return Carbon::parse($dateTimeStr, $timezoneFrom)->setTimezone($timezoneTo);
    }

    public function timezoneForPresentation()
    {
        return new \DateTimeZone($this->getPresentationTimeZoneString());
    }

    public function getPresentationTimeZoneString()
    {
        $timezone = session()->get(static::PRESENTATION_TIME_ZONE_SESSION_KEY);
        if (empty($timezone)) {
            $timezone = config('app.default_presentation_timezone', 'UTC');
        }

        return $timezone;
    }

    public function timezoneForStorage()
    {
        return new \DateTimeZone(config('app.timezone'));
    }

    public function fromTimestamp($timeStamp, \DateTimeZone $timezone = null)
    {
        $timezone = empty($timezone) ? $this->timezoneForStorage() : $timezone;

        $datetime = Carbon::now($timezone);
        $datetime->setTimestamp($timeStamp);

        return $datetime;
    }

    public function formatDate($dateTime, \DateTimeZone $timezone = null)
    {
        $viewDateTime = clone $dateTime;
        $timezone     = empty($timezone) ? $this->timezoneForPresentation() : $timezone;
        $viewDateTime->setTimeZone($timezone);

        return $viewDateTime->format('Y-m-d');
    }

    public function formatTime($dateTime, \DateTimeZone $timezone = null)
    {
        $viewDateTime = clone $dateTime;
        $timezone     = empty($timezone) ? $this->timezoneForPresentation() : $timezone;
        $viewDateTime->setTimeZone($timezone);

        return $viewDateTime->format('H:i');
    }

    public function formatDateTime($dateTime, $format = 'Y-m-d H:i', \DateTimeZone $timezone = null)
    {
        if (empty($dateTime)) {
            $dateTime = $this->now();
        }
        $viewDateTime = clone $dateTime;
        $timezone     = empty($timezone) ? $this->timezoneForPresentation() : $timezone;
        $viewDateTime->setTimeZone($timezone);

        return $viewDateTime->format($format);
    }

    public function now(\DateTimeZone $timezone = null)
    {
        $timezone = empty($timezone) ? $this->timezoneForStorage() : $timezone;

        return Carbon::now($timezone);
    }

    public function getDateFormatByLocale($locale = null)
    {
        return trans('config.datetime.format', 'Y-m-d');
    }

    public function convertToStorageDateTime($dateTimeString)
    {
        $viewDateTime = new Carbon($dateTimeString, $this->timezoneForPresentation());
        $dateTime     = clone $viewDateTime;
        $dateTime->setTimeZone($this->timezoneForStorage());

        return $dateTime;
    }

    public function changeToPresentationTimeZone($dateTime)
    {
        return $dateTime->setTimezone($this->timezoneForPresentation());
    }
}
