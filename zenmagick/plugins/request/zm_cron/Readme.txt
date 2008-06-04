A cron package for ZenMagick
============================


Usage
=====

        // ensure required classes are loaded
        $cron = ZMLoader::make('ZMCronJobs');
        if ($cron->isTimeToRun()) {
            foreach ($cron->getJobs(false, true) as $job) {
              echo $job['line']."<BR>";
                $cron->runJob($job);
            }
        }

