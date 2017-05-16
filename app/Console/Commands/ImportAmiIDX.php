<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Idx_stock_history;
use DateTime;
use DateTimeZone;

class ImportAmiIDX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Import:AmiIDX';

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
    public function handle()
    {
      //Cosmetic Process
      echo "Start Processing \n";
      $sign = $this->processSign('\\');
      //Get List File Name
      $datetimeStart = new DateTime('now',new DateTimeZone('Asia/Jakarta'));
      $logFiles = $datetimeStart->format('Y-m-d His').' - ImportAmiIDX.log';
      $directory = '';
      $files = Storage::disk('metstockidx')->files('nprcrs');

      if(!empty($files))
      {
        $this->write_log($logFiles, 'Start Looping Files');
        foreach($files as $file)
        {
          $this->write_log($logFiles, 'Start '.$file);
          $total = 0;
          $insert = 0;
          $ignore = 0;
          $rawData = array();
          $rawData = explode("\r\n",Storage::disk('metstockidx')->get($file));

          if(!empty($rawData))
          {
            unset($rawData[0]);
            foreach($rawData as $line)
            {
              //Cosmetic Process
              $sign = $this->processSign($sign);
              echo $sign."\r";
              //
              $fetchData = explode(',',$line);
              $import = \App\Idx_stock_history::firstOrCreate(
                [
                  'date' => date('Y-m-d',strtotime( $fetchData[0])),
                  'emiten_code' => $fetchData[1]
                ],
                [
                  'open'=> $fetchData[2],
                  'high' => $fetchData[3],
                  'low' => $fetchData[4],
                  'close' => $fetchData[5],
                  'volume' => $fetchData[6],
                  'frequency' => 0
                ]
              );
              $total ++;
              if($import->wasRecentlyCreated)
              {
                $insert ++;
              }else{
                $ignore ++;
              }
            }// end foreach
          }else{
            $this->write_log($logFiles, 'No Files Found When Explode!');
          }
          $this->write_log($logFiles, 'Total Record =  '.$total. ' Inserted = '. $insert.' Ignored = '.$ignore);
          $this->write_log($logFiles, 'End '.$file);
          $this->write_log($logFiles, 'Move '.$file);
          $this->movefile(basename($file),'metstockidx','nprcrs','prcrs');
          $this->write_log($logFiles, 'Moved '.$file);
        }//end foreach

      }else{
        $this->write_log($logFiles, 'No Files Found!');
      }
      echo "End Processing";
      //$this->movefile($pathinfo,$pathinfo['basename'],'metstockidx','nprcrs','prcrs');

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
