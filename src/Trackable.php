<?php

namespace Imtigger\LaravelJobStatus;

trait Trackable
{
    /** @var int $statusId */
    protected $statusId;
    protected $progressNow = 0;
    protected $progressMax = 0;

    protected function setProgressMax($value)
    {
        $this->update(['progress_max' => $value]);
        $this->progressMax = $value;
    }

    protected function setProgressNow($value, $every = 1, $textual_progress = null)
    {
        if ($value % $every == 0 || $value == $this->progressMax) {
            $updateValues = ['progress_now' => $value];
            if ($textual_progress !== null) {
                $updateValues['textual_progress'] = $textual_progress;
            }
            $this->update($updateValues);
        }
        $this->progressNow = $value;
    }

    protected function incrementProgress($offset = 1, $every = 1, $textual_progress = null)
    {
        $value = $this->progressNow + $offset;
        $this->setProgressNow($value, $every, $textual_progress);
    }

    protected function setTextualProgress(string $value)
    {
        $this->update(['textual_progress' => $value]);
    }

    protected function setInput(array $value)
    {
        $this->update(['input' => $value]);
    }

    protected function setOutput(array $value)
    {
        $this->update(['output' => $value]);
    }

    protected function update(array $data)
    {
        /** @var JobStatus $entityClass */
        $entityClass = app()->getAlias(JobStatus::class);
        /** @var JobStatus $status */
        $status = $entityClass::find($this->statusId);

        if ($status != null) {
            return $status->update($data);
        }
        return null;
    }

    protected function prepareStatus(array $data = [])
    {
        /** @var JobStatus $entityClass */
        $entityClass = app()->getAlias(JobStatus::class);

        $data = array_merge(["type" => $this->getDisplayName()], $data);
        /** @var JobStatus $status */
        $status = $entityClass::create($data);

        $this->statusId = $status->id;
    }

    protected function getDisplayName()
    {
        return method_exists($this, 'displayName') ? $this->displayName() : static::class;
    }

    public function getJobStatusId()
    {
        if ($this->statusId == null) {
            throw new \Exception("Failed to get jobStatusId, have you called \$this->prepareStatus() in __construct() of Job?");
        }

        return $this->statusId;
    }
}
