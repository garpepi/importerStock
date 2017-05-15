<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Idx_stock_history;
use Illuminate\Support\Facades\Storage;
use Excel;
use DateTime;
use DateTimeZone;

class Import extends Controller
{

    public function test()
    {
      //Config::set('app.timezone', 'Asia/Jakarta');
      dd( Config::get('app.timezone'));

    }

    public function importsidxTXTAMI()
    {
      //Get List File Name
      $directory = '';
      $files = Storage::disk('metstockidx')->files('nprcrs');
      foreach($files as $file)
      {
        $rawData = array();
        $rawData = explode("\r\n",Storage::disk('metstockidx')->get($file));

        if(!empty($rawData))
        {
          unset($rawData[0]);
          foreach($rawData as $line)
          {
            $fetchData = explode(',',$line);
            $datetime = new DateTime('now',new DateTimeZone('Asia/Jakarta'));
            $now = $datetime->format('Y-m-d H:i:s.');
            echo $now.' - ';

            /*
            DB::table('idx_stock_histories')->insert(
                [
                  'date' => date('Y-m-d',strtotime( $fetchData[0])),
                  'emiten_code' => $fetchData[1],
                  'open'=> $fetchData[2],
                  'high' => $fetchData[3],
                  'low' => $fetchData[4],
                  'close' => $fetchData[5],
                  'volume' => $fetchData[6],
                  'frequency' => 0,
                  'created_at' => $now
                ]
              );
            */
            $after = new DateTime('now',new DateTimeZone('Asia/Jakarta'));
            echo $after->format('Y-m-d H:i:s').'<br>';
          }
          dd('');
        }

      }

      $pathinfo = pathinfo(storage_path() . $files[0]);

      //$this->movefile($pathinfo,$pathinfo['basename'],'metstockidx','nprcrs','prcrs');

    }

    protected function readTXTAMIFile($pathinfo)
    {

    }

    protected function movefile($pathinfo,$fileName,$disk,$sourceDirectory,$destinationDirectory)
    {
      // Check for Renaming Files
      if(Storage::disk($disk)->exists($destinationDirectory.'/'.$fileName))
      {
        $pathinfo = $this->rename($pathinfo,$disk,$destinationDirectory.'/');
      }
      Storage::disk($disk)->move($sourceDirectory.'/'.$fileName,$destinationDirectory.'/'.$pathinfo['basename']);
      return TRUE;
    }

    protected function rename($filePath,$disk,$directory)
    {
      $counter = 0;
      if(Storage::disk($disk)->exists($directory.$filePath['basename']))
        {
          $counter++;
          $filePath['basename'] = $filePath['basename'].$counter;
          return $this->rename($filePath,$disk,$directory);
        }
        else
        {
          return $filePath;
        }
    }
}
