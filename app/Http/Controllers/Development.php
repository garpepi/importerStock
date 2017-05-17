<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Storage;
use Stock_idx_histories;

class Development extends Controller
{
    //
    private function getFile($file)
    {
        return storage_path('app/idx/'.$file);
    }
    public function excelIDX()
    {
      $files = Storage::disk('idx')->files('nprcrs');
      foreach($files as $file)
      {
        $datas = Excel::selectSheetsByIndex(0)->load($this->getFile($file))->all()->toArray();
        if(!empty($datas))
        {
          $date = substr(basename($file),0,10);
          foreach($datas as $data)
          {
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
          }
        }
      }

      dd('end');
    }
}
