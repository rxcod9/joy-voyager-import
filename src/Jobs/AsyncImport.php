<?php

namespace Joy\VoyagerImport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Joy\VoyagerImport\Notifications\Import;
use TCG\Voyager\Contracts\User;

class AsyncImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $import;
    public $file;
    public $disk;
    public $readerType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        User $user,
        $import,
        $file,
        $disk,
        $readerType
    ) {
        $this->user       = $user;
        $this->import     = $import;
        $this->file       = $file;
        $this->disk       = $disk;
        $this->readerType = $readerType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->import->import(storage_path('app/' . $this->file), $this->disk, $this->readerType);

        $this->user->notify(new Import($this->file));
    }
}
