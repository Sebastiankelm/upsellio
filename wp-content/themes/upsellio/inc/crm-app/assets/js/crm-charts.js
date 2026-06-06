(function () {
  "use strict";

  var instances = {};

  function fmtNum(v) {
    var n = Number(v);
    if (!isFinite(n)) return "0";
    if (Math.abs(n) >= 1000) {
      return n.toLocaleString("pl-PL", { maximumFractionDigits: 1 });
    }
    return n % 1 === 0 ? String(Math.round(n)) : n.toLocaleString("pl-PL", { maximumFractionDigits: 2 });
  }

  function baseOptions(type) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: {
        legend: {
          display: true,
          position: "bottom",
          labels: { boxWidth: 12, padding: 12, font: { size: 11 }, usePointStyle: true },
        },
        tooltip: {
          enabled: true,
          mode: "index",
          intersect: false,
          backgroundColor: "rgba(10, 20, 16, 0.92)",
          padding: 10,
          titleFont: { size: 12, weight: "600" },
          bodyFont: { size: 11 },
          callbacks: {
            label: function (ctx) {
              var label = ctx.dataset.label || "";
              var val = ctx.parsed && ctx.parsed.y !== undefined ? ctx.parsed.y : ctx.raw;
              return (label ? label + ": " : "") + fmtNum(val);
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { maxTicksLimit: type === "bar" ? 12 : 10, font: { size: 10 }, maxRotation: 45, minRotation: 0 },
        },
        y: {
          beginAtZero: true,
          grid: { color: "rgba(0,0,0,0.06)" },
          ticks: {
            font: { size: 10 },
            callback: function (v) {
              return fmtNum(v);
            },
          },
        },
      },
    };
  }

  function destroy(id) {
    if (instances[id]) {
      instances[id].destroy();
      delete instances[id];
    }
  }

  function whenReady(cb) {
    if (typeof cb !== "function") return;
    if (window.Chart) {
      cb();
      return;
    }
    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (window.Chart) {
        clearInterval(timer);
        cb();
      } else if (tries > 100) {
        clearInterval(timer);
      }
    }, 40);
  }

  function getCanvas(elOrId) {
    if (!elOrId) return null;
    if (typeof elOrId === "string") return document.getElementById(elOrId);
    return elOrId;
  }

  function hexAlpha(hex, alpha) {
    if (!hex || hex.indexOf("#") !== 0) return "rgba(10, 191, 163, " + alpha + ")";
    var h = hex.slice(1);
    if (h.length === 3) {
      h = h.split("").map(function (c) { return c + c; }).join("");
    }
    var r = parseInt(h.slice(0, 2), 16);
    var g = parseInt(h.slice(2, 4), 16);
    var b = parseInt(h.slice(4, 6), 16);
    return "rgba(" + r + "," + g + "," + b + "," + alpha + ")";
  }

  /**
   * @param {string|HTMLCanvasElement} canvasId
   * @param {string[]} labels
   * @param {{label:string,data:number[],color:string,fill?:boolean,dashed?:boolean,yAxisID?:string}[]} datasets
   * @param {object} [opts]
   */
  function lineChart(canvasId, labels, datasets, opts) {
    var canvas = getCanvas(canvasId);
    if (!canvas || !window.Chart) return null;
    destroy(canvas.id || String(canvasId));
    var options = baseOptions("line");
    if (opts && opts.legend === false) {
      options.plugins.legend.display = false;
    }
    var ds = (datasets || []).map(function (d) {
      var c = d.color || "#0ABFA3";
      return {
        label: d.label || "",
        data: d.data || [],
        borderColor: c,
        backgroundColor: d.fill !== false ? hexAlpha(c, 0.14) : "transparent",
        borderWidth: d.dashed ? 1.5 : 2,
        borderDash: d.dashed ? [5, 4] : [],
        pointRadius: 3,
        pointHoverRadius: 6,
        pointHitRadius: 14,
        tension: 0.3,
        fill: d.fill !== false,
        yAxisID: d.yAxisID || "y",
      };
    });
    var chart = new window.Chart(canvas, {
      type: "line",
      data: { labels: labels || [], datasets: ds },
      options: options,
    });
    if (canvas.id) instances[canvas.id] = chart;
    return chart;
  }

  /**
   * @param {string|HTMLCanvasElement} canvasId
   * @param {string[]} labels
   * @param {number[]} values
   * @param {string} label
   * @param {string} color
   */
  function barChart(canvasId, labels, values, label, color) {
    var canvas = getCanvas(canvasId);
    if (!canvas || !window.Chart) return null;
    destroy(canvas.id || String(canvasId));
    var c = color || "#0ABFA3";
    var chart = new window.Chart(canvas, {
      type: "bar",
      data: {
        labels: labels || [],
        datasets: [{
          label: label || "",
          data: values || [],
          backgroundColor: c + "99",
          borderColor: c,
          borderWidth: 1,
          borderRadius: 4,
          hoverBackgroundColor: c,
        }],
      },
      options: (function () {
        var o = baseOptions("bar");
        o.plugins.legend.display = false;
        return o;
      })(),
    });
    if (canvas.id) instances[canvas.id] = chart;
    return chart;
  }

  /**
   * Obiekt dat → wartość (sync audytu).
   */
  function lineFromSeries(canvasId, series, label, color) {
    var keys = Object.keys(series || {}).sort();
    var data = keys.map(function (k) {
      return Number(series[k] || 0);
    });
    return lineChart(canvasId, keys, [{ label: label, data: data, color: color, fill: true }], { legend: false });
  }

  function lineSeriesFromPairs(canvasId, labels, values, label, color) {
    return lineChart(
      canvasId,
      labels || [],
      [{ label: label || "", data: (values || []).map(function (v) { return Number(v || 0); }), color: color, fill: true }],
      { legend: false }
    );
  }

  /**
   * Bieżący vs poprzedni okres (analityka ruchu).
   */
  function lineCompare(canvasId, labels, current, previous, label, color) {
    return lineChart(
      canvasId,
      labels,
      [
        { label: label, data: current, color: color, fill: true, dashed: false },
        { label: label + " (poprz.)", data: previous, color: "#94a3b8", fill: false, dashed: true },
      ],
      {}
    );
  }

  function doughnutChart(canvasId, labels, values, colors, opts) {
    var canvas = getCanvas(canvasId);
    if (!canvas || !window.Chart) return null;
    destroy(canvas.id || String(canvasId));
    opts = opts || {};
    var chart = new window.Chart(canvas, {
      type: "doughnut",
      data: {
        labels: labels || [],
        datasets: [{
          data: values || [],
          backgroundColor: colors || ["#0ABFA3", "#94a3b8", "#f59e0b", "#ef4444"],
          borderWidth: 2,
          borderColor: "#fff",
          hoverOffset: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: opts.cutout || "62%",
        plugins: {
          legend: {
            display: opts.legend !== false,
            position: "bottom",
            labels: { boxWidth: 10, padding: 8, font: { size: 10 } },
          },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                var total = (ctx.dataset.data || []).reduce(function (a, b) { return a + Number(b || 0); }, 0);
                var val = Number(ctx.raw || 0);
                var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                return (ctx.label || "") + ": " + fmtNum(val) + " (" + pct + "%)";
              },
            },
          },
        },
      },
    });
    if (canvas.id) instances[canvas.id] = chart;
    return chart;
  }

  function horizontalBarChart(canvasId, labels, values, label, colors) {
    var canvas = getCanvas(canvasId);
    if (!canvas || !window.Chart) return null;
    destroy(canvas.id || String(canvasId));
    var bg = colors;
    if (!bg || !bg.length) {
      bg = (values || []).map(function (_, i) {
        return hexAlpha("#0ABFA3", 0.75 - (i * 0.04));
      });
    }
    var chart = new window.Chart(canvas, {
      type: "bar",
      data: {
        labels: labels || [],
        datasets: [{
          label: label || "",
          data: values || [],
          backgroundColor: bg,
          borderRadius: 4,
          borderSkipped: false,
        }],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            beginAtZero: true,
            grid: { color: "rgba(0,0,0,0.06)" },
            ticks: { font: { size: 10 }, callback: function (v) { return fmtNum(v); } },
          },
          y: {
            grid: { display: false },
            ticks: { font: { size: 10 }, autoSkip: false },
          },
        },
      },
    });
    if (canvas.id) instances[canvas.id] = chart;
    return chart;
  }

  function groupedBarChart(canvasId, labels, datasets) {
    var canvas = getCanvas(canvasId);
    if (!canvas || !window.Chart) return null;
    destroy(canvas.id || String(canvasId));
    var ds = (datasets || []).map(function (d) {
      return {
        label: d.label || "",
        data: d.data || [],
        backgroundColor: d.color || "#0ABFA3",
        borderRadius: 4,
      };
    });
    var chart = new window.Chart(canvas, {
      type: "bar",
      data: { labels: labels || [], datasets: ds },
      options: (function () {
        var o = baseOptions("bar");
        o.plugins.legend.position = "top";
        o.plugins.legend.labels.font = { size: 10 };
        return o;
      })(),
    });
    if (canvas.id) instances[canvas.id] = chart;
    return chart;
  }

  function scoreGauge(canvasId, score, max, color) {
    var s = Math.max(0, Math.min(max || 100, Number(score) || 0));
    var rest = (max || 100) - s;
    return doughnutChart(
      canvasId,
      ["Wynik", ""],
      [s, rest],
      [color || "#0ABFA3", "#e8e8e0"],
      { legend: false, cutout: "72%" }
    );
  }

  function scheduleInit(cb) {
    whenReady(cb);
  }

  window.upsCrmChart = {
    whenReady: whenReady,
    scheduleInit: scheduleInit,
    destroy: destroy,
    line: lineChart,
    bar: barChart,
    lineSeries: lineFromSeries,
    lineSeriesFromPairs: lineSeriesFromPairs,
    lineCompare: lineCompare,
    doughnut: doughnutChart,
    horizontalBar: horizontalBarChart,
    groupedBar: groupedBarChart,
    scoreGauge: scoreGauge,
    fmt: fmtNum,
  };

  window.upsCrmScheduleChartInit = scheduleInit;
  var pending = window.upsCrmPendingChartInits || [];
  if (pending.length) {
    pending.forEach(function (fn) {
      whenReady(fn);
    });
    window.upsCrmPendingChartInits = [];
  }
})();
