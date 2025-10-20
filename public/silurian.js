let trend = 0; // -1 fall, 0 steady, +1 rise
let channel = 'temp'; // current series
let score = 0;

// Core state that will be saved/loaded
const state = {
  co2: 4200,
  temp: 24.0,
  hum: 75,
  wspd: 22,
  pres: 1018,
  sea: 70,
  hour: 6,
  day: 12,
  month: 6,
  yearMa: 430.0, // 430 million years ago (Silurian window 443–419 Ma)
  trend: 0,
  series: 'temp',
  history: [] // for the chart
};

// Elements
const el = (id) => document.getElementById(id);
const tmpVal = el('tmpVal');
const humVal = el('humVal');
const wspdVal = el('wspdVal');
const pressureVal = el('pressureVal');
const seaVal = el('seaVal');
const co2Val = el('co2Val');
const timeVal = el('timeVal');

const co2i = el('co2i'), windi = el('windi'), presi = el('presi'), seai = el('seai');
const co2o = el('co2o'), windo = el('windo'), preso = el('preso'), seao = el('seao');

const btnRandom = el('btnRandom'), btnReset = el('btnReset');
const slotIn = el('slot'), titleIn = el('title');
const btnSave = el('btnSave'), btnLoad = el('btnLoad'), btnDelete = el('btnDelete');
const saveList = el('saveList');

// Tabs
const tabs = document.querySelectorAll('.tab');
const tabcs = document.querySelectorAll('.tabc');
const seriesSelect = el('seriesSelect');
const btnSteady = el('btnSteady');
const btnRise = el('btnRise');
const btnFall = el('btnFall');
const weatherIcon = el('weatherIcon');

// Tasks
const tasksEl = el('tasks');
const scoreEl = el('score');

// Chart
let chart;
const ctx = document.getElementById('trendChart').getContext('2d');

function initChart(){
  chart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [{ label: 'Temperature (°C)', data: [], tension: .25 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: {legend:{position:'bottom'}},
      scales: { x: { title: { display: true, text: 'Ticks' }}, y: { title: { display: true, text: 'Temperature (°C)'}} }
    }
  });
}

function setSeries(key){
  channel = key;
  state.series = key;
  const labelMap = { temp: 'Temperature (°C)', hum: 'Humidity (%)', pres: 'Pressure (hPa)', wspd: 'Wind (km/h)' };
  chart.data.datasets[0].label = labelMap[key];
  chart.options.scales.y.title.text = labelMap[key];
  chart.data.labels.length = 0; // reset
  chart.data.datasets[0].data.length = 0;
}

function ui(){
  tmpVal.textContent = `${state.temp.toFixed(1)} °C`;
  humVal.textContent = `${state.hum.toFixed(0)} %`;
  wspdVal.textContent = `${state.wspd.toFixed(0)} km/h`;
  pressureVal.textContent = `${state.pres.toFixed(0)} hPa`;
  seaVal.textContent = `${state.sea >= 0 ? '+' : ''}${state.sea} m`;
  co2Val.textContent = `${state.co2} ppm`;
  timeVal.textContent = `${String(state.hour).padStart(2,'0')}:00 — ${state.day}/${state.month}/${state.yearMa.toFixed(0)} Ma`;
  co2i.value = state.co2; co2o.value = state.co2;
  windi.value = state.wspd; windo.value = state.wspd;
  presi.value = state.pres; preso.value = state.pres;
  seai.value = state.sea; seao.value = state.sea;
  // fun little icon switch
  weatherIcon.textContent = state.pres < 995 ? '🌪️' : state.wspd > 60 ? '💨' : state.co2 > 6000 ? '🔥' : '🌿';
}

function tick(){
  // basic diurnal wiggles + trend bias
  const bias = trend * 0.06; // gentle push
  const hour = state.hour;
  let dT = (hour>10 && hour<18) ? 0.15 : -0.1;
  state.temp = clamp(state.temp + dT + bias, 10, 50);
  state.hum = clamp(state.hum + (dT<0? +0.4 : -0.3) + (trend===1? +0.1 : trend===-1? -0.1 : 0), 10, 100);
  state.wspd = clamp(state.wspd * (1 + (Math.random() - 0.5) * 0.02 + bias*0.02), 0, 160);
  state.pres = clamp(state.pres + (Math.random()-0.5)*0.6 + (trend===1? +0.15 : trend===-1? -0.15 : 0), 960, 1045);

  // clock
  state.hour += 1; if (state.hour >= 24){ state.hour = 0; state.day += 1; }
  if (state.day > 30){ state.day = 1; state.month += 1; }
  if (state.month > 12){ state.month = 1; state.yearMa -= 0.01; } // tiny forward march in geologic time

  // push sample to chart
  const sample = channel==='temp'? state.temp : channel==='hum'? state.hum : channel==='pres'? state.pres : state.wspd;
  chart.data.labels.push(chart.data.labels.length+1);
  chart.data.datasets[0].data.push(Number(sample.toFixed(2)));
  if (chart.data.labels.length>180){ chart.data.labels.shift(); chart.data.datasets[0].data.shift(); }

  ui();
  scoreLogic();
}

function scoreLogic(){
  // Silurian-friendly heuristics
  let s = 0;
  if (between(state.temp, 18, 30)) s += 3; else s += Math.max(0, 3 - dist(state.temp, [18,30]));
  if (between(state.hum, 50, 90)) s += 2; else s += Math.max(0, 2 - dist(state.hum, [50,90]));
  if (between(state.pres, 1005, 1025)) s += 2; else s += Math.max(0, 2 - dist(state.pres, [1005,1025])/5);
  if (between(state.wspd, 0, 60)) s += 1; else s += Math.max(0, 1 - dist(state.wspd, [0,60])/10);
  score = Math.round(Math.min(100, (s/8)*100));
  scoreEl.textContent = String(score);
}

function clamp(v,min,max){return Math.max(min, Math.min(max, v));}
function between(v,a,b){return v>=a && v<=b;}
function dist(v,[a,b]){return v<a? (a-v): v>b? (v-b): 0;}

// Controls
co2i.addEventListener('input', e=>{ state.co2 = Number(e.target.value); co2o.value = state.co2; ui();});
windi.addEventListener('input', e=>{ state.wspd = Number(e.target.value); windo.value = state.wspd; ui();});
presi.addEventListener('input', e=>{ state.pres = Number(e.target.value); preso.value = state.pres; ui();});
seai.addEventListener('input', e=>{ state.sea = Number(e.target.value); seao.value = state.sea; ui();});

btnRandom.addEventListener('click', ()=>{
  state.co2 = Math.round(2000 + Math.random()*5000);
  state.wspd = Math.round(Math.random()*100);
  state.pres = Math.round(985 + Math.random()*50);
  state.sea  = Math.round(-100 + Math.random()*200);
  ui();
});

btnReset.addEventListener('click', ()=>{
  Object.assign(state, {co2:4200,temp:24.0,hum:75,wspd:22,pres:1018,sea:70,hour:6,day:12,month:6,yearMa:430,trend:0,series:'temp'});
  setSeries('temp');
  ui();
});

// Tabs
for (const t of tabs){
  t.addEventListener('click', ()=>{
    tabs.forEach(x=>x.classList.remove('active'));
    tabcs.forEach(x=>x.classList.remove('active'));
    t.classList.add('active');
    const id = t.dataset.tab;
    document.getElementById('tab-'+id).classList.add('active');
  });
}
seriesSelect.addEventListener('change', (e)=> setSeries(e.target.value));
btnSteady.addEventListener('click', ()=> trend=0);
btnRise.addEventListener('click', ()=> trend=1);
btnFall.addEventListener('click', ()=> trend=-1);

// API helpers
async function api(path, opts){
  const res = await fetch(path, Object.assign({ headers: { 'Content-Type': 'application/json' }}, opts));
  const data = await res.json();
  if (!res.ok || data.ok === false) throw new Error(data.error || 'Request failed');
  return data;
}

async function refreshList(){
  try {
    const data = await api('/api/list.php');
    saveList.innerHTML = '';
    for (const row of data.saves){
      const li = document.createElement('li');
      li.textContent = `${row.slot} — ${row.title || 'untitled'} (updated ${row.updated_at})`;
      saveList.appendChild(li);
    }
  } catch(e){ console.warn(e); }
}

btnSave.addEventListener('click', async ()=>{
  const slot = (slotIn.value || '').trim();
  if (!/^[\w-]{1,40}$/.test(slot)) { alert('Pick a slot like "myworld" (letters, numbers, -, _)'); return; }
  const title = (titleIn.value || '').trim() || 'Silurian run';
  const payload = Object.assign({}, state, { trend, series: channel });
  try {
    await api('/api/save.php', { method: 'POST', body: JSON.stringify({ slot, title, state: payload })});
    alert('Saved!');
    refreshList();
  } catch(e){ alert('Save failed: ' + e.message); }
});

btnLoad.addEventListener('click', async ()=>{
  const slot = (slotIn.value || '').trim();
  if (!slot) { alert('Enter a slot to load'); return; }
  try {
    const data = await api('/api/load.php?slot=' + encodeURIComponent(slot));
    Object.assign(state, data.state);
    trend = state.trend || 0; channel = state.series || 'temp';
    setSeries(channel);
    ui();
    alert('Loaded!');
  } catch(e){ alert('Load failed: ' + e.message); }
});

btnDelete.addEventListener('click', async ()=>{
  const slot = (slotIn.value || '').trim();
  if (!slot) { alert('Enter a slot to delete'); return; }
  if (!confirm('Delete save "' + slot + '"?')) return;
  try {
    await api('/api/delete.php', { method:'POST', body: JSON.stringify({ slot }) });
    alert('Deleted.');
    refreshList();
  } catch(e){ alert('Delete failed: ' + e.message); }
});

// Boot
initChart();
setSeries('temp');
ui();
refreshList();
setInterval(tick, 1000);
