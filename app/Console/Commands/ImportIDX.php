<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Excel;
use Stock_idx_histories;
use DateTime;
use DateTimeZone;

class ImportIDX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Import:IDX';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        //Cosmetic Process
        echo "Start Processing \n";
        $sign = $this->processSign('\\');
        //Get List File Name
        $datetimeStart = new DateTime('now',new DateTimeZone('Asia/Jakarta'));
        $logFiles = $datetimeStart->format('Y-m-d His').' - ImportAmiIDX.log';
        // Start
        $files = Storage::disk('idx')->files('nprcrs');
        if(!empty($files))
        {
          $this->write_log($logFiles, 'Start Looping Files');
          foreach($files as $file)
          {
            //Cosmetic Process
            $sign = $this->processSign($sign);
            echo $sign."\r";
            $total = 0;
            $insert = 0;
            $ignore = 0;
            $datas = Excel::selectSheetsByIndex(0)->load($this->getFile($file))->all()->toArray();
            if(!empty($datas))
            {
              $this->write_log($logFiles, 'Start '.$file);
              $date = substr(basename($file),0,10);
              foreach($datas as $data)
              {
                //Cosmetic Process
                $sign = $this->processSign($sign);
                echo $sign."\r";
                $import = \App\Stock_idx_histories::firstOrCreate(
                  [
                    'date' => date('Y-m-d',strtotime($date)),
                    'emiten_code' => $data['stock_code']
                  ],
                  [
                    'open'=> $data['open_price'],
                    'high' => $data['high'],
                    'low' => $data['low'],
                    'close' => $data['close'],
                    'volume' => $data['volume'],
                    'frequency' => $data['frequency']
                  ]
                );
                $total ++;
                if($import->wasRecentlyCreated)
                {
                  $insert ++;
                }else{
                  $ignore ++;
                }
              }
            }else
            {
              $this->write_log($logFiles, 'Data Empty! Exitting'.$file);
            }
            $this->write_log($logFiles, 'Total Record =  '.$total. ' Inserted = '. $insert.' Ignored = '.$ignore);
            $this->write_log($logFiles, 'End '.$file);
            $this->write_log($logFiles, 'Move '.$file);
            $this->movefile(basename($file),'idx','nprcrs','prcrs');
            $this->write_log($logFiles, 'Moved '.$file);
          }
        }else{
          $this->write_log($logFiles, 'No Files Found!');
        }

        echo "End Processing";
    }

    /*
    * Other Function
    *
    */
    private function getFile($file)
    {
        return storage_path('app/idx/'.$file);
    }

    private function write_log($fileName,$content)
    {
      $datetime = new DateTime('now',new DateTimeZone('Asia/Jakarta'));
      Storage::disk('local-log')->append($fileName,$datetime->format('Y-m-d H:i:s').' - '.$content."\n");
    }

    private function processSign($lastSign)
    {
      if($lastSign =='|')
      {
        return '/';
      }elseif($lastSign == '/')
      {
        return '-';
      }elseif($lastSign == '-')
      {
        return '\\';
      }else
      {
        return '|';
      }
    }

    private function movefile($fileName,$disk,$sourceDirectory,$destinationDirectory)
    {
      // Check for Renaming Files
      if(Storage::disk($disk)->exists($destinationDirectory.'/'.$fileName))
      {
        $newName = $this->rename($fileName,$disk,$destinationDirectory.'/');
      }else{
        $newName = $fileName;
      }
      Storage::disk($disk)->move($sourceDirectory.'/'.$fileName,$destinationDirectory.'/'.$newName);
      return TRUE;
    }

    private function rename($fileName,$disk,$directory)
    {
      $counter = 0;
      if(Storage::disk($disk)->exists($directory.$fileName))
        {
          $counter++;
          $fileName = $fileName.$counter;
          return $this->rename($fileName,$disk,$directory);
        }
        else
        {
          return $fileName;
        }
    }
}
