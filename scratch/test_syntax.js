
    document.addEventListener('DOMContentLoaded', function() {
        const charts = {};
        if(Object.keys(charts).length === 0) return;

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748B';
        const commonOptions = { responsive: true, maintainAspectRatio: false };

        const colors = {
            'Operation': '#5AA2D7', 
            'Maintenance': '#ED7D31', 
            'HSSE': '#A5A5A5', 
            'Business Support': '#FFC000',
            'Safe Action': '#5AA2D7',
            'Safe Condition': '#ED7D31',
            'Unsafe Action': '#A5A5A5',
            'Unsafe Condition': '#FFC000',
            'Tidak ada temuan': '#94a3b8',
        };

        // Helper to get array of values and labels from assoc array
        const getLabels = (obj) => Object.keys(obj);
        const getValues = (obj) => Object.values(obj);
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        // 1. Pie Chart - % PEKA Per Fungsi
        const chart1Labels = getLabels(charts.fungsi);
        const chart1Values = getValues(charts.fungsi);
        const chart1Total = chart1Values.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('chart1'), {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: { 
                labels: chart1Labels, 
                datasets: [{ 
                    data: chart1Values, 
                    backgroundColor: getBgColors(chart1Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }] 
            },
            options: { 
                ...commonOptions, 
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: {
                            weight: 'bold',
                            size: 16,
                            family: "'Inter', sans-serif"
                        },
                        formatter: (value) => {
                            if (value === 0 || chart1Total === 0) return null;
                            const pct = ((value / chart1Total) * 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = chart1Total > 0 ? ((val / chart1Total) * 100).toFixed(1) : 0;
                                return [
                                    ' ' + val.toLocaleString('id-ID') + ' Pelaporan',
                                    ' ' + pct + '%'
                                ];
                            }
                        }
                    }
                } 
            }
        });

        // 2. Horizontal Bar - Reporting Rate per Fungsi
        const rrChart   = charts.reporting || {};
        const rrRawData = rrChart.data || {};
        const rrMode    = rrChart.mode || 'distribusi';

        // Filter null values (manpower tidak tersedia) dan exclude AREA LHD
        const rrLabels   = Object.keys(rrRawData).filter(k => k !== 'AREA LHD' && rrRawData[k] !== null);
        const rrValues   = rrLabels.map(k => rrRawData[k]);

        const rrColorMap = {
            'Operation': '#5B9BD5',
            'Maintenance': '#ED7D31',
            'HSSE': '#A5A5A5',
            'Business Support': '#FFC000'
        };
        const rrBgColors = rrLabels.map(l => rrColorMap[l] || '#9CA3AF');

        const chart2El = document.getElementById('chart2');
        if (chart2El && rrLabels.length > 0) {
            new Chart(chart2El, {
                type: 'bar',
                data: { 
                    labels: rrLabels, 
                    datasets: [{ data: rrValues, backgroundColor: rrBgColors, borderRadius: 4 }] 
                },
                plugins: [
                    {
                        id: 'rrValueLabels',
                        afterDatasetsDraw(chartInstance) {
                            const ctx = chartInstance.ctx;
                            ctx.save();
                            ctx.font = "bold 14px 'Inter', sans-serif";
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#1e293b';

                            chartInstance.data.datasets.forEach(function (dataset, i) {
                                const meta = chartInstance.getDatasetMeta(i);
                                meta.data.forEach(function (bar, index) {
                                    const data = dataset.data[index];
                                    if (data !== null && data !== undefined) {
                                        ctx.fillText(data, bar.x + 8, bar.y);
                                    }
                                });
                            });
                            ctx.restore();
                        }
                    }
                ],
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    layout: {
                        padding: {
                            right: 60
                        }
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            titleFont: {
                                size: 15,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.raw;
                                    return rrMode === 'manpower_rasio'
                                        ? ` ${val} (temuan YTD ÷ manpower × bulan)`
                                        : ` ${val}`;
                                }
                            }
                        }
                    },
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            grace: '15%',
                            grid: { display: false },
                            ticks: {
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        },
                        y: { 
                            grid: { display: false },
                            ticks: {
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        }
                    }
                }
            });
        }

        // 3. Pie Chart - Kategori PEKA
        const chart3Labels = getLabels(charts.kategori);
        const chart3Values = getValues(charts.kategori);
        const chart3Total = chart3Values.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('chart3'), {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: { 
                labels: chart3Labels, 
                datasets: [{ 
                    data: chart3Values, 
                    backgroundColor: getBgColors(chart3Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }] 
            },
            options: { 
                ...commonOptions, 
                plugins: { 
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: {
                            weight: 'bold',
                            size: 16,
                            family: "'Inter', sans-serif"
                        },
                        formatter: (value) => {
                            if (value === 0 || chart3Total === 0) return null;
                            const pct = ((value / chart3Total) * 100).toFixed(2).replace('.', ',');
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = chart3Total > 0
                                    ? ((val / chart3Total) * 100).toFixed(2).replace('.', ',')
                                    : '0,00';
                                return [
                                    ' ' + val.toLocaleString('id-ID') + ' Pelaporan',
                                    ' ' + pct + '%'
                                ];
                            }
                        }
                    }
                } 
            }
        });

        // Hitung & tampilkan statistik Positif / Negatif (di bawah pie)
        if (chart3Total > 0) {
            const positifKeys = ['Safe Action', 'Safe Condition'];
            const negatifKeys  = ['Unsafe Action', 'Unsafe Condition'];
            const positifTotal = positifKeys.reduce((sum, k) => sum + (charts.kategori[k] || 0), 0);
            const negatifTotal  = negatifKeys.reduce((sum, k) => sum + (charts.kategori[k] || 0), 0);
            const fmt = (n) => (chart3Total > 0 ? (n / chart3Total * 100).toFixed(2) : '0.00').replace('.', ',');
            const statsEl = document.getElementById('chart3-stats');
            if (statsEl) {
                document.getElementById('chart3-positif').textContent = fmt(positifTotal) + '%';
                document.getElementById('chart3-negatif').textContent  = fmt(negatifTotal)  + '%';
                statsEl.classList.remove('hidden');
            }
        }

        // ================================================================
        // 4. Stacked Vertical Bar - Keterlibatan Observasi (%)
        // FIX: Chart SELALU dirender selama ada minimal 1 fungsi.
        //   - Fungsi null / not ok → placeholder abu-abu (data belum tersedia / N/A)
        //   - Fungsi float + ok   → bar normal (Keterlibatan + Sisa 100%)
        // ================================================================
        (function() {
            const invRaw    = charts.keterlibatan || {};
            const invLabels = Object.keys(invRaw);

            if (invLabels.length === 0) return;

            const koStatus = {};
            const isOk     = koStatus === 'ok';

            // ---- Siapkan data chart -----------------------------------------
            const invData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.min(invRaw[k], 100) : 0);
            const remData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.max(100 - Math.min(invRaw[k], 100), 0) : 100);

            // Warna: fungsi dengan data → normal; fungsi null / error → abu-abu
            const ketBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#ED7D31' : 'transparent');
            const sisBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#5AA2D7'  : '#E2E8F0');

            // ---- Render Chart ------------------------------------------------
            new Chart(document.getElementById('chart4'), {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: invLabels,
                    datasets: [
                        {
                            label: 'Keterlibatan',
                            data: invData,
                            backgroundColor: ketBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    return isOk && invRaw[key] !== null && invData[ctx.dataIndex] > 0;
                                },
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                                formatter: (value, ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    if (!isOk || invRaw[key] === null) return null;
                                    const val = Math.round(invRaw[key]);
                                    return val > 0 ? val + '%' : null;
                                }
                            }
                        },
                        {
                            label: 'Belum Melapor',
                            data: remData,
                            backgroundColor: sisBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    return !isOk || invRaw[key] === null;
                                },
                                color: '#94A3B8',
                                anchor: 'center',
                                align: 'center',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                formatter: () => 'N/A',
                            }
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    onHover: (event, chartElement) => {
                        const canvas = event.native?.target || document.getElementById('chart4');
                        if (canvas) {
                            canvas.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        }
                    },
                    onClick: (event, elements) => {
                        if (!elements || elements.length === 0) return;
                        const el = elements[0];
                        const key = invLabels[el.index];
                        if (!key) return;

                        if (typeof window.openUnreportedModal === 'function') {
                            window.openUnreportedModal(key);
                        }
                    },
                    scales: {
                        x: { 
                            stacked: true, 
                            grid: { display: false },
                            ticks: {
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        },
                        y: {
                            stacked: true,
                            max: 100,
                            ticks: { 
                                callback: v => v + '%',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: {
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 15,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const key = invLabels[ctx.dataIndex];
                                    if (!isOk || invRaw[key] === null) {
                                        if (ctx.datasetIndex === 1) {
                                            if (koStatus === 'no_sipeka') {
                                                return ' Data temuan SIPEKA belum tersedia';
                                            } else if (koStatus === 'no_manpower') {
                                                return ' Data manpower belum tersedia';
                                            } else if (koStatus === 'no_month') {
                                                return ' Pilih Bulan untuk menampilkan persentase';
                                            }
                                            return ' Data belum tersedia';
                                        }
                                        return null;
                                    }
                                    if (ctx.datasetIndex === 1) return null;
                                    return ` Keterlibatan: ${invRaw[key].toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    })}%`;
                                }
                            }
                        }
                    }
                }
            });
        })();

        // 5. Stacked Vertical Bar - Rekap % Temuan Fungsi
        const ptLabels = getLabels(charts.persentase_fungsi);
        const closedData = ptLabels.map(l => charts.persentase_fungsi[l].closed);
        const openData = ptLabels.map(l => charts.persentase_fungsi[l].open);
        
        new Chart(document.getElementById('chart5'), {
            type: 'bar',
            plugins: [ChartDataLabels],
            data: {
                labels: ptLabels,
                datasets: [
                    {
                        label: 'Closed',
                        data: closedData,
                        backgroundColor: '#548235',
                        datalabels: {
                            display: (ctx) => {
                                const val = closedData[ctx.dataIndex];
                                return val !== null && val !== undefined && Math.round(val) > 0;
                            },
                            color: '#fff',
                            anchor: 'center',
                            align: 'center',
                            font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                            formatter: (value) => {
                                const val = Math.round(value);
                                return val > 0 ? val + '%' : null;
                            }
                        }
                    },
                    {
                        label: 'TOTAL (Open)',
                        data: openData,
                        backgroundColor: '#FFC000',
                        datalabels: {
                            display: false
                        }
                    }
                ]
            },
            options: { 
                ...commonOptions, 
                scales: { 
                    x: { 
                        stacked: true, 
                        grid: { display: false },
                        ticks: {
                            font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b'
                        }
                    }, 
                    y: { 
                        stacked: true, 
                        max: 100, 
                        ticks: { 
                            callback: v => v + '%',
                            font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b'
                        } 
                    } 
                },
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                return ` ${ctx.dataset.label}: ${val}%`;
                            }
                        }
                    }
                }
            }
        });

        // 6. Donut Chart - Rekap % Penindak Lanjut Temuan (SAP)
        new Chart(document.getElementById('chart6'), {
            type: 'doughnut',
            data: { 
                labels: getLabels(charts.tindak_lanjut), 
                datasets: [{ 
                    data: getValues(charts.tindak_lanjut), 
                    backgroundColor: getBgColors(getLabels(charts.tindak_lanjut)) 
                }] 
            },
            options: { 
                ...commonOptions, 
                plugins: { 
                    legend: { 
                        position: 'right',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12
                    }
                } 
            }
        });

        // Helper to format long bilingual category labels into controlled multi-line arrays
        const formatBilingualLabel = (label, maxChars = 38) => {
            if (!label) return '';
            let idPart = label;
            let enPart = '';

            if (label.includes(' / ')) {
                const parts = label.split(' / ');
                idPart = parts[0].trim();
                enPart = parts.slice(1).join(' / ').trim();
            } else {
                // Match slash followed by English translation keywords
                const match = label.match(/^(.*?)\s*\/\s*(Failure|Using|Improper|Placement|operating|Inadequate|Incorrect|Defective|Mode|material|Restricted|Poor|PPE)(.*)$/i);
                if (match) {
                    idPart = match[1].trim();
                    enPart = (match[2] + match[3]).trim();
                }
            }

            const wrap = (str, max) => {
                if (str.length <= max) return [str];
                const words = str.split(' ');
                const lines = [];
                let curr = '';
                for (const w of words) {
                    if ((curr ? curr + ' ' + w : w).length <= max) {
                        curr = curr ? curr + ' ' + w : w;
                    } else {
                        if (curr) lines.push(curr);
                        curr = w;
                    }
                }
                if (curr) lines.push(curr);
                return lines;
            };

            if (!enPart) {
                return wrap(label, maxChars);
            }

            return [...wrap(idPart, maxChars), ...wrap(enPart, maxChars)];
        };

        // Helper for Horizontal Bar charts with Data Labels (Percentages & Counts)
        // Urutan kategori mengikuti backend (PHP array order) — TIDAK di-sort by value.
        const renderHorizontalBar = (ctxId, chartData, color) => {
            // Pertahankan urutan dari backend (PHP array order)
            let dataArr = Object.entries(chartData.data || {})
                .map(([k, v]) => ({
                    rawLabel: k,
                    formattedLabel: formatBilingualLabel(k, 38),
                    value: v
                }));
            // CATATAN: tidak ada .sort() — urutan ditentukan oleh backend service
            
            const total = chartData.total || 1; // Prevent division by zero
            
            new Chart(document.getElementById(ctxId), {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: { 
                    labels: dataArr.map(d => d.formattedLabel), 
                    datasets: [{ 
                        data: dataArr.map(d => ((d.value / total) * 100).toFixed(1)), 
                        backgroundColor: color,
                        borderWidth: 0,
                        borderRadius: 4,
                        barThickness: 16,  // proporsional untuk 2-line label
                        minBarLength: 6    // bar dengan nilai > 0 selalu terlihat & mudah di-hover
                    }] 
                },
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 90 // ruang luas agar data label persentase + count utuh dan tidak terpotong
                        }
                    },
                    interaction: {
                        mode: 'index',
                        axis: 'y',
                        intersect: false // hover di mana saja sepanjang baris akan memunculkan tooltip
                    },
                    plugins: { 
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            offset: 4,
                            color: '#1e293b',
                            font: {
                                weight: 'bold',
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            formatter: function(value, context) {
                                let rawCount = dataArr[context.dataIndex].value;
                                return `${value}% (${rawCount})`;
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 13,
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                title: function(context) {
                                    const idx = context[0].dataIndex;
                                    return dataArr[idx].rawLabel || context[0].label;
                                },
                                label: function(context) {
                                    let val = context.raw;
                                    let rawCount = dataArr[context.dataIndex].value;
                                    return ` ${val}% (${rawCount} temuan)`;
                                }
                            }
                        }
                    }, 
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            max: 100,
                            grid: { display: true, color: '#f1f5f9' },
                            ticks: {
                                maxRotation: 0,
                                autoSkip: true,
                                font: { size: 11, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }, 
                        y: { 
                            grid: { display: false },
                            afterFit: (scale) => { scale.width = 250; }, // ruang proporsional agar label tidak terpotong dan bar leluasa
                            ticks: {
                                autoSkip: false,
                                font: { size: 11, weight: '600', family: "'Inter', sans-serif", lineHeight: 1.25 },
                                color: '#1e293b'
                            }
                        } 
                    }
                }
            });
        };

        // 7. Horizontal Bar - Unsafe Action
        renderHorizontalBar('chart7', charts.unsafe_action, '#ED7D31');

        // 8. Horizontal Bar - Unsafe Condition
        renderHorizontalBar('chart8', charts.unsafe_condition, '#ED7D31');

        // ================================================================
        // GRAFIK GLOBAL — Analisis PEKA Seluruh Fungsi
        // Data dari $globalCharts (getCharts(null, ...)) — agregat semua fungsi.
        // ID elemen: global-chart1 ... global-chart8 (tidak konflik dengan chart1–8).
        // ================================================================
        const globalCharts = {};
        if (Object.keys(globalCharts).length === 0) return;

        // G1. Pie Chart - Jumlah Pelaporan per Fungsi (Global)
        const gc1Labels = getLabels(globalCharts.fungsi);
        const gc1Values = getValues(globalCharts.fungsi);
        const gc1Total  = gc1Values.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('global-chart1'), {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: {
                labels: gc1Labels,
                datasets: [{
                    data: gc1Values,
                    backgroundColor: getBgColors(gc1Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                        formatter: (value) => {
                            if (value === 0 || gc1Total === 0) return null;
                            const pct = ((value / gc1Total) * 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = gc1Total > 0 ? ((val / gc1Total) * 100).toFixed(1) : 0;
                                return [' ' + val.toLocaleString('id-ID') + ' Pelaporan', ' ' + pct + '%'];
                            }
                        }
                    }
                }
            }
        });

        // G2. Horizontal Bar - Reporting Rate per Fungsi (Global)
        const gRrChart   = globalCharts.reporting || {};
        const gRrRawData = gRrChart.data || {};
        const gRrMode    = gRrChart.mode || 'distribusi';
        const gRrLabels  = Object.keys(gRrRawData).filter(k => k !== 'AREA LHD' && gRrRawData[k] !== null);
        const gRrValues  = gRrLabels.map(k => gRrRawData[k]);
        const gRrColorMap = {
            'Operation': '#5B9BD5', 'Maintenance': '#ED7D31',
            'HSSE': '#A5A5A5', 'Business Support': '#FFC000'
        };
        const gRrBgColors = gRrLabels.map(l => gRrColorMap[l] || '#9CA3AF');

        const gc2El = document.getElementById('global-chart2');
        if (gc2El && gRrLabels.length > 0) {
            new Chart(gc2El, {
                type: 'bar',
                data: {
                    labels: gRrLabels,
                    datasets: [{ data: gRrValues, backgroundColor: gRrBgColors, borderRadius: 4 }]
                },
                plugins: [{
                    id: 'gRrValueLabels',
                    afterDatasetsDraw(chartInstance) {
                        const ctx = chartInstance.ctx;
                        ctx.save();
                        ctx.font = "bold 14px 'Inter', sans-serif";
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#1e293b';
                        chartInstance.data.datasets.forEach(function(dataset, i) {
                            const meta = chartInstance.getDatasetMeta(i);
                            meta.data.forEach(function(bar, index) {
                                const data = dataset.data[index];
                                if (data !== null && data !== undefined) {
                                    ctx.fillText(data, bar.x + 8, bar.y);
                                }
                            });
                        });
                        ctx.restore();
                    }
                }],
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    layout: { padding: { right: 60 } },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: (ctx) => {
                                    const val = ctx.raw;
                                    return gRrMode === 'manpower_rasio'
                                        ? ` ${val} (temuan YTD ÷ manpower × bulan)`
                                        : ` ${val}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grace: '15%',
                            grid: { display: false },
                            ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
                        }
                    }
                }
            });
        }

        // G3. Pie Chart - Kategori PEKA (Global)
        const gc3Labels = getLabels(globalCharts.kategori);
        const gc3Values = getValues(globalCharts.kategori);
        const gc3Total  = gc3Values.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('global-chart3'), {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: {
                labels: gc3Labels,
                datasets: [{
                    data: gc3Values,
                    backgroundColor: getBgColors(gc3Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                        formatter: (value) => {
                            if (value === 0 || gc3Total === 0) return null;
                            const pct = ((value / gc3Total) * 100).toFixed(2).replace('.', ',');
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = gc3Total > 0
                                    ? ((val / gc3Total) * 100).toFixed(2).replace('.', ',')
                                    : '0,00';
                                return [' ' + val.toLocaleString('id-ID') + ' Pelaporan', ' ' + pct + '%'];
                            }
                        }
                    }
                }
            }
        });

        // Statistik Positif / Negatif Global Chart 3
        if (gc3Total > 0) {
            const gPositifKeys = ['Safe Action', 'Safe Condition'];
            const gNegatifKeys = ['Unsafe Action', 'Unsafe Condition'];
            const gPositifTotal = gPositifKeys.reduce((sum, k) => sum + (globalCharts.kategori[k] || 0), 0);
            const gNegatifTotal = gNegatifKeys.reduce((sum, k) => sum + (globalCharts.kategori[k] || 0), 0);
            const gFmt = (n) => (gc3Total > 0 ? (n / gc3Total * 100).toFixed(2) : '0.00').replace('.', ',');
            const gStatsEl = document.getElementById('global-chart3-stats');
            if (gStatsEl) {
                document.getElementById('global-chart3-positif').textContent = gFmt(gPositifTotal) + '%';
                document.getElementById('global-chart3-negatif').textContent = gFmt(gNegatifTotal) + '%';
                gStatsEl.classList.remove('hidden');
            }
        }

        // G4. Stacked Vertical Bar - Keterlibatan Observasi (Global)
        // Klik bar memanggil openUnreportedModal(fungsi) — otorisasi dijaga di backend:
        // Admin/Manager Function hanya bisa akses fungsi mereka sendiri (HTTP 403 jika lintas fungsi).
        (function() {
            const gInvRaw    = globalCharts.keterlibatan || {};
            const gInvLabels = Object.keys(gInvRaw);
            if (gInvLabels.length === 0) return;

            const gIsOk = gInvLabels.some(k => gInvRaw[k] !== null);

            const gInvData = gInvLabels.map(k => (gInvRaw[k] !== null) ? Math.min(gInvRaw[k], 100) : 0);
            const gRemData = gInvLabels.map(k => (gInvRaw[k] !== null) ? Math.max(100 - Math.min(gInvRaw[k], 100), 0) : 100);

            const gKetBgColors = gInvLabels.map(k => (gInvRaw[k] !== null) ? '#ED7D31' : 'transparent');
            const gSisBgColors = gInvLabels.map(k => (gInvRaw[k] !== null) ? '#5AA2D7' : '#E2E8F0');

            new Chart(document.getElementById('global-chart4'), {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: gInvLabels,
                    datasets: [
                        {
                            label: 'Keterlibatan',
                            data: gInvData,
                            backgroundColor: gKetBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    const key = gInvLabels[ctx.dataIndex];
                                    return gInvRaw[key] !== null && gInvData[ctx.dataIndex] > 0;
                                },
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                                formatter: (value, ctx) => {
                                    const key = gInvLabels[ctx.dataIndex];
                                    if (gInvRaw[key] === null) return null;
                                    const val = Math.round(gInvRaw[key]);
                                    return val > 0 ? val + '%' : null;
                                }
                            }
                        },
                        {
                            label: 'Belum Melapor',
                            data: gRemData,
                            backgroundColor: gSisBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    const key = gInvLabels[ctx.dataIndex];
                                    return gInvRaw[key] === null;
                                },
                                color: '#94A3B8',
                                anchor: 'center',
                                align: 'center',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                formatter: () => 'N/A',
                            }
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    onHover: (event, chartElement) => {
                        const canvas = event.native?.target || document.getElementById('global-chart4');
                        if (canvas) canvas.style.cursor = chartElement[0] ? 'pointer' : 'default';
                    },
                    onClick: (event, elements) => {
                        if (!elements || elements.length === 0) return;
                        const el = elements[0];
                        const key = gInvLabels[el.index];
                        if (!key) return;
                        // Otorisasi dijaga di backend — Admin/Manager Function hanya bisa
                        // akses fungsinya sendiri; request lintas fungsi akan mendapat 403.
                        if (typeof window.openUnreportedModal === 'function') {
                            window.openUnreportedModal(key);
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
                        },
                        y: {
                            stacked: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const key = gInvLabels[ctx.dataIndex];
                                    if (gInvRaw[key] === null) {
                                        if (ctx.datasetIndex === 1) return ' Data belum tersedia';
                                        return null;
                                    }
                                    if (ctx.datasetIndex === 1) return null;
                                    return ` Keterlibatan: ${gInvRaw[key].toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%`;
                                }
                            }
                        }
                    }
                }
            });
        })();

        // G5. Stacked Vertical Bar - Rekap % Temuan per Fungsi (Global)
        const gPtLabels   = getLabels(globalCharts.persentase_fungsi);
        const gClosedData = gPtLabels.map(l => globalCharts.persentase_fungsi[l].closed);
        const gOpenData   = gPtLabels.map(l => globalCharts.persentase_fungsi[l].open);

        new Chart(document.getElementById('global-chart5'), {
            type: 'bar',
            plugins: [ChartDataLabels],
            data: {
                labels: gPtLabels,
                datasets: [
                    {
                        label: 'Closed',
                        data: gClosedData,
                        backgroundColor: '#548235',
                        datalabels: {
                            display: (ctx) => {
                                const val = gClosedData[ctx.dataIndex];
                                return val !== null && val !== undefined && Math.round(val) > 0;
                            },
                            color: '#fff',
                            anchor: 'center',
                            align: 'center',
                            font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                            formatter: (value) => {
                                const val = Math.round(value);
                                return val > 0 ? val + '%' : null;
                            }
                        }
                    },
                    {
                        label: 'TOTAL (Open)',
                        data: gOpenData,
                        backgroundColor: '#FFC000',
                        datalabels: { display: false }
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
                    },
                    y: {
                        stacked: true,
                        max: 100,
                        ticks: {
                            callback: v => v + '%',
                            font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                return ` ${ctx.dataset.label}: ${val}%`;
                            }
                        }
                    }
                }
            }
        });

        // G6. Donut Chart - Rekap % Penindak Lanjut (Global)
        new Chart(document.getElementById('global-chart6'), {
            type: 'doughnut',
            data: {
                labels: getLabels(globalCharts.tindak_lanjut),
                datasets: [{
                    data: getValues(globalCharts.tindak_lanjut),
                    backgroundColor: getBgColors(getLabels(globalCharts.tindak_lanjut))
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                        padding: 12
                    }
                }
            }
        });

        // G7. Horizontal Bar - Unsafe Action (Global)
        renderHorizontalBar('global-chart7', globalCharts.unsafe_action, '#ED7D31');

        // G8. Horizontal Bar - Unsafe Condition (Global)
        renderHorizontalBar('global-chart8', globalCharts.unsafe_condition, '#ED7D31');

    });

