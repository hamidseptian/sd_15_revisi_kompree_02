<?php
session_start();
include "../../../../assets/koneksi.php";
require_once("../../../../assets/dompdf/src/Autoloader.php");
Dompdf\Autoloader::register();
use Dompdf\Dompdf;

$id_ta_aktif = $_GET['id_ta'];
$id_kelas = 1;//$_GET['id_kelas'];
$semester_ke = 2;//$_GET['semester'];
$id_wk = $_SESSION['id_user'];
$semester = [1=>'Ganjil','Genap'];




$qta = mysqli_query($conn, "SELECT * from tahun_ajaran where id_ta='$id_ta_aktif' and semester='$semester_ke'");
$jta = mysqli_num_rows ($qta);
$dta = mysqli_fetch_array($qta);
$id_ta = $dta['id_ta'];
$status_ta = $dta['status_ta'];
$semester_aktif = $dta['semester'];
$qkelas = mysqli_query($conn, "SELECT * from kelas where id_kelas='$id_kelas'");
$dkelas = mysqli_fetch_array($qkelas);


$qcekwk = mysqli_query($conn, "SELECT b.nama_guru, a.status_wali_kelas, a.id_guru, b.nip, a.username, a.id_walikelas from wali_kelas a left join guru b on a.id_guru = b.id_guru where a.id_kelas='$id_kelas' order by id_walikelas desc limit 1");
$jcekwk = mysqli_num_rows($qcekwk);
$dcekwk = mysqli_fetch_array($qcekwk);
$id_wk_aktif = $dcekwk['id_guru'];
$id_wali_kelas = $dcekwk['id_walikelas'];




$html = '

<center>
  <h3>LAPORAN HUTANG PIUTANG SISWA PER TAHUN AJARAN</h3>
</center>
<hr>  
<table style="font-size:14px;border-collapse: collapse;" >
  
    ';


if ($jta==0) {
            $qta_selesai = mysqli_query($conn, "SELECT * from tahun_ajaran where id_ta='$id_ta_aktif'");
            $dta_selesai = mysqli_fetch_array($qta_selesai);

$html .= '


   <tr>
        <td width = "200px">Tahun Ajaran</td>
        <td>'.$dta_selesai['nama_ta'].'</td>
      </tr>
  
      </table>
      
    ';

$nama_ta = $dta_selesai['nama_ta'];
            }
            else{

$html .= '


   <tr>
        <td width = "200px">Tahun Ajaran</td>
        <td>'.$dta['nama_ta'].'</td>
      </tr>
   <tr>
        <td width = "200px">Semester</td>
        <td>'.$semester[$dta['semester']].'</td>
      </tr>

   <tr>
        <td width = "200px">Wali Kelas</td>
        <td>'.$dcekwk['nama_guru'].'</td>
      </tr>
      </table>
      
    ';
    $nama_ta = $dta['nama_ta'];
            }



$html .= '
<br>';



$html .= '
 <table style="font-size:12px;border-collapse: collapse; width:100%" border = 1>
    <thead>
      <tr>
      <th  width="20px">No</th>
        <th  width="50px">NIS</th>
        <th>Nama siswa</th>
        <th width="50px">Kelas</th>
        <th>Nama Item</th>
        <th width="50px">Jumlah Item</th>
     
      <td width="50px">Total</td>
      <td width="50px">Dibayar</td>
      <td width="50px">Sisa</td>
      </tr>
        
     

';







// a.status_ks, a.id_siswa,  b.nama_siswa, b.nis, b.nisn, b.jk, b.agama, b.tmpl, b.tgll, c.tingkat


$no=1;
$mapel = mysqli_query($conn, "SELECT 
  pg.*,
    s.nama_siswa, s.nis, s.alamat, s.no_telp, s.id_siswa, s.tmpl, s.tgll,s.jk, 
    k.tingkat, k.nama_kelas 
    from pengambilan pg
    left join kelas_siswa ks on pg.id_siswa=ks.id_siswa
    left join siswa s on ks.id_siswa = s.id_siswa
    left join kelas k on ks.id_kelas = k.id_kelas
    left join tahun_ajaran ta on ks.id_ta = ta.id_ta
    where s.status_siswa = 'Aktif'
    and ta.id_ta='$id_ta_aktif'
    group by pg.id_pengambilan");
  while ($data=mysqli_fetch_array($mapel))
  { 

    $id_siswa = $data['id_siswa'];
 $q2=mysqli_query($conn, "SELECT count(id_pengambilan) as qty, sum(biaya) as biaya from pengambilan where id_siswa='$id_siswa'
    ");
      $d2 = mysqli_fetch_array($q2);
      $q3=mysqli_query($conn, "SELECT sum(jumlah) as dibayar from pembayaran where id_siswa='$id_siswa'
    ");
      $d3 = mysqli_fetch_array($q3);
        $hutang = $d2['biaya'];
    $dibayar = $d3['dibayar'] =='' ? 0 : $d3['dibayar'];
    $sisa = $hutang - $dibayar;



$pt = explode('-', $data['tgll']);
$tgll = $pt[2].'-'.$pt[1].'-'.$pt[0];
$html .= '
   <tr>     
  ';
$html .= '
  <td>'.$no++.'</td>
  

    
  <td>'.$data['nis'].'</td>
  <td>'.$data['nama_siswa'].'</td>
  <td>'.$data['tingkat'].' - '.$data['nama_kelas'].'</td>
  <td>'.$data['barang'].'</td>
  <td>1</td>
  ';

  
$html .= '
 

    
  <td>'.number_format($hutang).'</td>
  <td>'.number_format($dibayar).'</td>
  <td>'.number_format($sisa).'</td>';
 





$html .= '
   </tr>     
  ';
}

  $html .= '
  </table>
  <br>
    <div style="float:right;text-align:center; font-size:12px">
    Padang, '.date('d').' '.$nama_bulan[date('m')].' '.date('Y').'<br>
    Bendahara
 <br> <br> <br> <br>
Vivit Triani <br>
    </div>

    <div style="float:left;text-align:center; font-size:12px">
   <br>
       Kepala  SD Nageri 15 Padang Pasir
 <br> <br> <br> <br>
ROHANI, S.Pd <br>
NIP : 19660821 199005 2 001
    </div>

';

$dompdf = new Dompdf();

$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream('Data Hutang Piutang Siswa Tahun Ajaran '.$nama_ta.'.pdf', ['Attachment'=>0]);

?>
<p style="font-size: "></p>

