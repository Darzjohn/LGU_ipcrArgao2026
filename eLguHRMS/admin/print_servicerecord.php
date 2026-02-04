<?php
require_once __DIR__ . '/../auth/session_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

/* ================= SIGNATORIES ================= */
$approved_by = ['name'=>'ALLAN M. SESALDO','position'=>'Municipal Mayor'];
$prepared_by = ['name'=>'JO ANN M. VILLAFUERTE','position'=>'Administrative Officer V'];

$q = $mysqli->query("SELECT position,name,title FROM signatories");
if($q){
    while($s=$q->fetch_assoc()){
        if(stripos($s['position'],'mayor')!==false || stripos($s['title'],'mayor')!==false){
            $approved_by=['name'=>$s['name'],'position'=>'Municipal Mayor'];
        }
        if(stripos($s['position'],'admin')!==false || stripos($s['title'],'personnel')!==false){
            $prepared_by=['name'=>$s['name'],'position'=>$s['position']];
        }
    }
}

/* ================= IDS ================= */
$ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));
if(!$ids) exit('No records selected');
$ph = implode(',', array_fill(0,count($ids),'?'));

/* ================= QUERY ================= */
$sql = "
SELECT sr.*, e.first_name,e.middle_name,e.surname,e.name_extension,e.dob,
       d.name department_name, p.name position_name, es.name status_name
FROM service_records sr
LEFT JOIN employees e ON sr.emp_idno=e.emp_idno
LEFT JOIN departments d ON sr.assignment=d.id
LEFT JOIN positions p ON sr.position=p.id
LEFT JOIN employment_status es ON sr.status=es.id
WHERE sr.id IN ($ph)
ORDER BY sr.emp_idno, sr.recfrom ASC
";
$stmt=$mysqli->prepare($sql);
$stmt->bind_param(str_repeat('i',count($ids)), ...$ids);
$stmt->execute();
$res=$stmt->get_result();

$data=[];
while($r=$res->fetch_assoc()){
    $data[$r['emp_idno']]['emp']=$r;
    $data[$r['emp_idno']]['rows'][]=$r;
}

/* ================= PDF ================= */
$pdf = new TCPDF('P','mm','LEGAL',true,'UTF-8',false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15,10,15);
$pdf->SetAutoPageBreak(true,18);
$pdf->SetFont('dejavusans','',8);

$m = $pdf->getMargins();

/* ================= TEXT ================= */
$note="Date herein should be checked from the birth or baptismal certificate or some other reliable document/paper";
$cert="This is to certify that the employee named herein above actually rendered services in this Office as shown by the employee service record, below, each line of which is supported by appointment and other papers actually issued by this Office and approved by the authorities concerned.";

/* ================= COLUMNS ================= */
$col = [28,28,35,30,28,20,14,40,18,22,28,26];

/* AUTO FIT */
$usableWidth = $pdf->getPageWidth() - $m['left'] - $m['right'];
$scale = $usableWidth / array_sum($col);
$col = array_map(fn($w)=>round($w*$scale,2), $col);

/* ================= HELPERS ================= */
function cellH($pdf,$w,$t){
    return max(8,$pdf->getStringHeight($w,$t));
}

function vMultiCell($pdf,$w,$h,$txt,$border=1,$align='L',$fill=false){
    $x=$pdf->GetX();
    $y=$pdf->GetY();
    $th=$pdf->getStringHeight($w,$txt);
    $yoff=($h-$th)/2;

    if($border){
        $pdf->Rect($x,$y,$w,$h);
    }

    $pdf->SetXY($x,$y+max(0,$yoff));
    $pdf->MultiCell($w,$th,$txt,0,$align,$fill);
    $pdf->SetXY($x+$w,$y);
}

function tableHeader($pdf,$col,$note,$showNote=true){
    $m=$pdf->getMargins();

    if($showNote){
        $pdf->SetFont('','I',8);
        $pdf->MultiCell(0,5,$note);
        $pdf->Ln(2);
    }

    $pdf->SetX($m['left']);
    $pdf->SetFont('','B',6);

    $h=[
        "FROM","TO","DESIGNATION","STATUS","SALARY",
        "SALARY\nGRADE","STEP","OFFICE\nASSIGNMENT",
        "LAWOP","SEPARATION\nDATE","SEPARATION\nCLAUSE","REMARKS"
    ];

    foreach($h as $i=>$t){
        vMultiCell($pdf,$col[$i],12,$t,1,'C');
    }
    $pdf->Ln(12);
    $pdf->SetFont('','',7);
}

/* ================= LOOP ================= */
foreach($data as $d){

$e=$d['emp'];
$rows=$d['rows'];

$pdf->AddPage();
$firstPageOfReport = $pdf->getPage();   // ⭐ capture first page

/* HEADER */
$pdf->Cell(0,4,'REPUBLIC OF THE PHILIPPINES',0,1,'C');
$pdf->Cell(0,4,'PROVINCE OF CEBU',0,1,'C');
$pdf->Cell(0,4,'MUNICIPALITY OF ARGAO',0,1,'C');
$pdf->Ln(2);

$pdf->SetFont('','B',12);
$pdf->Cell(0,6,'SERVICE RECORD',0,1,'C');
$pdf->SetFont('','',9);
$pdf->Cell(0,5,'SERVICE RECORD OF APPOINTMENT',0,1,'C');
$pdf->Cell(0,4,'LOCAL GOVERNMENT UNIT',0,1,'C');
$pdf->Ln(3);

/* EMP INFO */
$name=strtoupper(trim($e['first_name'].' '.$e['middle_name'].' '.$e['surname'].' '.$e['name_extension']));
$dob=$e['dob']?date('F j, Y',strtotime($e['dob'])):'';

$pdf->Cell(30,6,'NAME:',0,0);
$pdf->SetFont('','B');
$pdf->Cell(0,6,$name,0,1);
$pdf->SetFont('');
$pdf->Cell(30,6,'BIRTHDATE:',0,0);
$pdf->Cell(0,6,$dob,0,1);
$pdf->Ln(4);

/* TABLE */
tableHeader($pdf,$col,$note,true);

foreach($rows as $r){

$to=($r['recto'] && $r['recto']!='0000-00-00') ? date('m/d/Y',strtotime($r['recto'])):'Present';

$cells=[
 date('m/d/Y',strtotime($r['recfrom'])),
 $to,
 $r['position_name'],
 $r['status_name'],
 number_format((float)$r['salary'],2),
 $r['salary_grade'],
 $r['step_increment'],
 $r['department_name'],
 $r['lawop'],
 $r['separation_date']?date('m/d/Y',strtotime($r['separation_date'])):'',
 $r['separation_cause'],
 $r['remarks']
];

$rh=8;
foreach($cells as $i=>$t){
    $rh=max($rh,cellH($pdf,$col[$i],$t));
}

if($pdf->GetY()+$rh>($pdf->getPageHeight()-20)){
    $pdf->AddPage();
    tableHeader($pdf,$col,$note,false);
}

foreach($cells as $i=>$t){
    $a='L';
    if(in_array($i,[0,1,3,5,6,8,9])) $a='C';
    if($i==4) $a='R';
    vMultiCell($pdf,$col[$i],$rh,$t,1,$a);
}
$pdf->Ln($rh);
}

/* CERTIFICATION */
$pdf->Ln(6);
$pdf->MultiCell(0,5,$cert);
$pdf->Ln(10);

/* SIGNATURES */
$w = ($pdf->getPageWidth() - $m['left'] - $m['right']) / 2;
$pdf->SetX($m['left'] - 8);
$pdf->Cell($w,6,'Approved By:',0,0,'C');
$pdf->Cell($w,6,'Prepared By:',0,1,'C');
$pdf->Ln(18);

$pdf->SetFont('','BU');
$pdf->Cell($w,6,strtoupper($approved_by['name']),0,0,'C');
$pdf->Cell($w,6,strtoupper($prepared_by['name']),0,1,'C');

$pdf->Ln(-2);
$pdf->SetFont('');
$pdf->Cell($w,4,'MUNICIPAL MAYOR',0,0,'C');
$pdf->Cell($w,4,strtoupper($prepared_by['position']),0,1,'C');

// /* ===== DATE ACCOMPLISHED (BOTTOM OF FIRST PAGE ONLY) ===== */
// $lastPage = $pdf->getPage();
// $pdf->setPage($firstPageOfReport);
// $pdf->SetY(-15);
// $pdf->SetFont('helvetica','',9);
// $pdf->Cell(0,6,'Date Accomplished: '.date('F j, Y'),0,0,'C');
// $pdf->setPage($lastPage);

/* ===== DATE ACCOMPLISHED (BOTTOM OF FIRST PAGE ONLY) ===== */

// Save state
$lastPage   = $pdf->getPage();
$autoBreak  = $pdf->getAutoPageBreak();
$breakMargin= $pdf->getBreakMargin();

// Force write on first page without page breaking
$pdf->setAutoPageBreak(false, 0);
$pdf->setPage($firstPageOfReport);

// Absolute Y from top (LETTER = 279.4mm)
$pageHeight = $pdf->getPageHeight();
$bottomY    = $pageHeight - 15;

$pdf->SetY($bottomY);
$pdf->SetFont('helvetica','',9);
$pdf->Cell(0,6,'Date Accomplished: '.date('F j, Y'),0,0,'C');

// Restore state
$pdf->setAutoPageBreak($autoBreak, $breakMargin);
$pdf->setPage($lastPage);


}

$pdf->Output('service_record.pdf','I');
exit;
