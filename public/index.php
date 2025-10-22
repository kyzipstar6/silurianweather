
<?php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Silurian Climate Simulator</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Silurian Climate Simulator</h1>
      <p class="sub">Save your run, reload later, and keep evolving your world.</p>
    </header>

    <section class="columns">
      <div class="panel">
        <h2>Current Conditions</h2>
        <div class="big-icon" id="weatherIcon">🌿</div>
        <div class="grid">
          <div class="kv"><span>Temperature</span><strong id="tmpVal">24.0 °C</strong></div>
          <div class="kv"><span>Humidity</span><strong id="humVal">75 %</strong></div>
          <div class="kv"><span>Wind Speed</span><strong id="wspdVal">22 km/h</strong></div>
          <div class="kv"><span>Pressure</span><strong id="pressureVal">1018 hPa</strong></div>
          <div class="kv"><span>Sea Level</span><strong id="seaVal">+70 m</strong></div>
          <div class="kv"><span>Atmospheric CO₂</span><strong id="co2Val">4200 ppm</strong></div>
          <div class="kv"><span>Day & Date</span><strong id="timeVal">06:00 — 12/6/430 Ma</strong></div>
        </div>
      </div>

      <div class="panel">
        <h2>Controls</h2>
        <label class="lbl">CO₂ (ppm)
          <input id="co2i" type="range" min="1000" max="8000" value="4200" />
          <output id="co2o">4200</output>
        </label>
        <label class="lbl">Wind (km/h)
          <input id="windi" type="range" min="0" max="120" value="22" />
          <output id="windo">22</output>
        </label>
        <label class="lbl">Pressure (hPa)
          <input id="presi" type="range" min="980" max="1045" value="1018" />
          <output id="preso">1018</output>
        </label>
        <label class="lbl">Sea level (m)
          <input id="seai" type="range" min="-200" max="300" value="70" />
          <output id="seao">70</output>
        </label>

        <div class="row">
          <button id="btnRandom" class="btn">Randomize</button>
          <button id="btnReset" class="btn ghost">Reset</button>
        </div>

        <h3>Save & Continue</h3>
        <div class="row">
          <input id="slot" placeholder="slot-name (e.g. myworld)" maxlength="40" />
          <input id="title" placeholder="Optional title" />
        </div>
        <div class="row">
          <button id="btnSave" class="btn">Save</button>
          <button id="btnLoad" class="btn ghost">Load</button>
          <button id="btnDelete" class="btn danger ghost">Delete</button>
        </div>

        <details class="mt">
          <summary>Available saves</summary>
          <ul id="saveList"></ul>
        </details>
      </div>
    </section>

    <section class="panel">
      <div class="tabs">
        <button class="tab active" data-tab="trend">Climate Trend</button>
        <button class="tab" data-tab="tasks">Research Tasks</button>
      </div>
      <div id="tab-trend" class="tabc active">
        <div class="row">
          <select id="seriesSelect">
            <option value="temp" selected>Temperature</option>
            <option value="hum">Humidity</option>
            <option value="pres">Pressure</option>
            <option value="wspd">Wind speed</option>
          </select>
          <button id="btnSteady" class="btn ghost">Steady</button>
          <button id="btnRise" class="btn ghost">Rise</button>
          <button id="btnFall" class="btn ghost">Fall</button>
        </div>
        <canvas id="trendChart"></canvas>
      </div>
      <div id="tab-tasks" class="tabc">
        <p>Keep variables within Silurian-friendly ranges to score points.</p>
        <ul id="tasks"></ul>
        <div class="score">Score: <strong id="score">0</strong></div>
      </div>
    </section>

    <footer>
      <small>Built for Silurian exploration. Save/Load powered by PHP + SQLite/Postgres. Source-ready for Render & GitHub Pages (static mirror).
      </small>
    </footer>
  </div>

  <script src="/silurian.js"></script>
</body>
</html>
