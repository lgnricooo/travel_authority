<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>{{ $t->tracking_no }}</title>
<link href="https://googleapis.com" rel="stylesheet">
<style>
  @page{size:A4;margin:0}              /* margin 0 also removes the browser's date/URL header and footer */
  *{box-sizing:border-box}
  html,body{margin:0}
  body{background:#e9ecef;font-family:"Bookman Old Style","Book Antiqua",Georgia,serif;font-size:10.5pt;color:#000}

  /* one A4 sheet: form on top, letterhead footer pinned to the bottom */
  .page{width:210mm;height:296mm;margin:10px auto;background:#fff;box-shadow:0 0 6px rgba(0,0,0,.25);
        padding:11mm 14mm 9mm;display:flex;flex-direction:column;overflow:hidden}
  .body{flex:1;min-height:0;overflow:hidden;display:flex;flex-direction:column}
  #fit{flex:1;min-height:0;display:flex;flex-direction:column}
  #fit>*{flex:none}
  #fit>.box{flex:1 1 auto}

  .hd{position:relative;text-align:center;border-bottom:2.5px solid #000;padding-bottom:2px}
  .hd img.seal{height:20mm}
  .qr{position:absolute;top:0;right:0;width:23mm;text-align:center}
  .qr #qr{width:23mm;height:23mm;border:1px solid #000}
  .qr .cap{font-size:5.5pt;line-height:1.1;margin-top:1px}
  .bl{font-family:"Old English Text MT","UnifrakturCook",serif;font-weight:700;line-height:1.05}
  .hd .s{font-weight:700;font-size:9.5pt;line-height:1.2}
  .no{width:62mm;margin:5px 0 0 auto;border:1px solid #000;padding:3px 8px;font-weight:700}
  h2{text-align:center;font-size:13pt;text-decoration:underline;margin:10px 0 8px;letter-spacing:.5px}
  table{width:100%;border-collapse:collapse}
  td{border:1px solid #000;padding:3px 6px;vertical-align:top}
  td.l{width:33%;font-weight:700}td.l small{font-weight:400;font-size:9.5pt;display:block}

  .box{border:1px solid #000;border-top:0;padding:3px 6px 4px;display:flex;flex-direction:column}
  .box .row{margin-top:auto}
  .box i{font-size:8pt;line-height:1.15;display:block}
  .row{display:flex;justify-content:space-between;align-items:flex-end;padding:0 16px}
  .who{text-align:center;flex:0 0 66%}
  .sig{height:14mm;display:flex;align-items:flex-end;justify-content:center}.sig img{max-height:13mm;max-width:55mm}
  .nm{font-weight:700;text-decoration:underline;text-transform:uppercase;font-size:10pt}
  .ds{font-size:9.5pt;line-height:1.15}
  .en{font-size:6.5pt;font-style:italic;color:#222;line-height:1.15;margin-top:1px}
  .dt{text-align:center;flex:0 0 26%}.dt .v{text-decoration:underline;font-size:10pt}
  .appr{font-weight:700;font-size:9.5pt}
  .copy{margin-top:4px;padding-top:2px;border-top:1px dotted #888;text-align:center;font-size:6.8pt;color:#222;line-height:1.2}

  .foot{flex:none;margin-top:4px;border-top:2.5px solid #000;padding-top:5px;display:flex;align-items:center;gap:8px;
        font-family:Calibri,Arial,sans-serif;font-size:8.5pt;line-height:1.25}
  .foot img{height:15mm}.foot .t{margin-left:6px}

  .memo-page{width:210mm;height:296mm;margin:10px auto;background:#fff;box-shadow:0 0 6px rgba(0,0,0,.25);
             padding:8mm 10mm;display:flex;flex-direction:column;align-items:center;overflow:hidden;
             page-break-before:always;break-before:page}
  .memo-page .cap{align-self:flex-start;font-size:7pt;color:#444;margin-bottom:3mm}
  .memo-page canvas,.memo-page img{max-width:100%;max-height:270mm;width:auto;height:auto;object-fit:contain}
  .memo-note{font-size:10pt;margin-top:8px}
  .noprint{position:fixed;top:10px;right:10px;padding:6px 14px;z-index:1000;cursor:pointer}

  @media print{
    body{background:#fff}
    .page,.memo-page{margin:0;box-shadow:none}
    .noprint,.memo-note{display:none}
  }
</style></head>
<body>
@php $req = $t->party('requester'); $rec = $t->party('recommender'); $app = $t->party('approver'); @endphp
<button class="noprint" id="printBtn" onclick="window.print()">Print</button>

<div class="page">
 <div class="body"><div id="fit">
  <div class="hd">
    @if($t->verification_code)<div class="qr"><div id="qr"></div><div class="cap">Scan to verify</div></div>@endif
    <img class="seal" src="{{ asset('images/deped-seal.png') }}" alt="">
    <div class="bl" style="font-size:11pt">Republic of the Philippines</div>
    <div class="bl" style="font-size:20pt">Department of Education</div>
    <div class="s">Region II – Cagayan Valley</div>
    <div class="s">SCHOOLS DIVISION OF TUGUEGARAO CITY</div>
  </div>

  <div class="no">NO.: {{ $t->tracking_no }}</div>
  <h2>TRAVEL AUTHORITY FOR OFFICIAL TRAVEL</h2>

  <table>
    <tr><td class="l">NAME</td><td style="text-transform:uppercase">{{ $req['name'] }}</td></tr>
    <tr><td class="l">Position/Designation</td><td>{{ $req['position'] }}</td></tr>
    <tr><td class="l">Permanent Station</td><td>{{ $req['office'] }}</td></tr>
    <tr><td class="l" style="height:17mm">Purpose of Travel<small>(must be supported by attachments)</small></td><td>{!! nl2br(e($t->purpose)) !!}</td></tr>
    <tr><td class="l">Host of Activity</td><td>{{ $t->host_of_activity }}</td></tr>
    <tr><td class="l">Inclusive Dates</td><td>{{ $t->inclusiveDates() }}</td></tr>
    <tr><td class="l">Destination</td><td>{{ $t->destination }}</td></tr>
    <tr><td class="l">Fund Source</td><td>{{ $t->fund_source }}</td></tr>
  </table>

  {{-- Requester attestation --}}
  <div class="box">
    <i>I hereby attest that the information in this form and in the supporting documents attached hereto are true and correct.</i>
    <div class="row">
      <div class="who"><div class="sig">@if($t->requester_signature)<img src="{{ Storage::disk('s3')->url($t->requester_signature) }}" alt="">@endif</div><div class="nm">{{ $req['name'] }}</div><div class="ds">{{ $req['position'] }}</div><div class="en">Electronically signed {{ $t->created_at->format('d/m/Y h:i A') }} to attest that the information in this form is true and correct</div></div>
      <div class="dt"><div class="v">{{ $t->created_at->format('d/m/Y') }}</div><div class="ds">Date</div></div>
    </div>
  </div>

  {{-- Recommending officer certification --}}
  <div class="box">
    <i>This is to certify that the trip of the requesting employee satisfies all the minimum conditions for authorized official travel and that alternatives to travel are insufficient for purpose stated herein.</i>
    <div class="row">
      <div class="who"><div class="sig">@if($t->recommender_signature)<img src="{{ Storage::disk('s3')->url($t->recommender_signature) }}" alt="">@endif</div><div class="nm">{{ $rec['name'] }}</div><div class="ds">{{ $rec['position'] }}</div><div class="en">Electronically signed {{ $t->recommended_at?->format('d/m/Y h:i A') }} to certify and recommend approval</div></div>
      <div class="dt"><div class="v">{{ $t->recommended_at?->format('d/m/Y') }}</div><div class="ds">Date</div></div>
    </div>
  </div>

  {{-- Approval --}}
  <div class="box">
    <div class="appr">APPROVED</div>
    <div class="row">
      <div class="who"><div class="sig">@if($t->approver_signature)<img src="{{ Storage::disk('s3')->url($t->approver_signature) }}" alt="">@endif</div><div class="nm">{{ $app['name'] }}</div><div class="ds">{{ $app['position'] }}</div><div class="en">Electronically signed {{ $t->decided_at?->format('d/m/Y h:i A') }} to approve this travel authority</div></div>
      <div class="dt"><div class="v">{{ $t->decided_at?->format('d/m/Y') }}</div><div class="ds">Date</div></div>
    </div>
  <div class="copy">This printout is a secondary copy. The original of this document is in electronic format and bears electronic signatures.
    @if($t->verification_code)<br>Verify: {{ route('verify', $t->verification_code) }} &bull; Code: <b>{{ $t->verification_code }}</b>@endif</div>
  </div>


 </div></div>

  <div class="foot">
    <img src="{{ asset('images/bagong-pilipinas.png') }}" alt="">
    <img src="{{ asset('images/sdo-seal.png') }}" alt="">
    <div class="t">
      <b>Address:</b> Regional Government Center, Carig Sur, Tuguegarao City, 3500<br>
      <b>Telephone Nos.:</b> (078) 844-7925; (078) 377-8805<br>
      <b>Email Address:</b> tuguegarao@deped.gov.ph<br>
      <b>Website:</b> depedtuguegarao.net
    </div>
  </div>
</div>

@if($t->memo_path && !$t->memoIsPdf())
<div class="memo-page"><div class="cap">Attachment: Memorandum — {{ $t->tracking_no }}</div><img src="{{ $t->memoUrl() }}" alt=""></div>
@endif

@if($t->memoIsPdf())
<div id="memoPages"></div>
<p id="memoMsg" class="memo-note" style="text-align:center">Preparing the memo pages…</p>
<script src="https://cloudflare.com"></script>
<script>
// Draw every page of the memo PDF onto its own A4 sheet so the whole memo prints (an embedded PDF viewer would not).
(async function () {
  const msg = document.getElementById('memoMsg'), host = document.getElementById('memoPages'), btn = document.getElementById('printBtn');
  btn.disabled = true; btn.textContent = 'Preparing…';
  try {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cloudflare.com';
    const pdf = await pdfjsLib.getDocument({ url: @json($t->memoUrl()), withCredentials: true }).promise;
    for (let n = 1; n <= pdf.numPages; n++) {
      const page = await pdf.getPage(n);
      const base = page.getViewport({ scale: 1 });
      const vp = page.getViewport({ scale: Math.min(3, Math.max(1, 1700 / base.width)) });
      const canvas = document.createElement('canvas');
      canvas.width = Math.floor(vp.width); canvas.height = Math.floor(vp.height);
      await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
      const sheet = document.createElement('div'); sheet.className = 'memo-page';
      const cap = document.createElement('div'); cap.className = 'cap';
      cap.textContent = 'Attachment: Memorandum — {{ $t->tracking_no }} — page ' + n + ' of ' + pdf.numPages;
      sheet.append(cap, canvas); host.append(sheet);
    }
    msg.remove();
  } catch (e) {
