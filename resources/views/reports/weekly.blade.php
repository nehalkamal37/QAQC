@extends('layouts.app')
@section('content')


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
     
        
        body {
            background-color: #f5f7fa;
            color: #333;
           /* padding: 20px;*/
            line-height: 1.6;
        }
        
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .report-header {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            padding: 30px;
            position: relative;
        }
        
        .report-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
        }
        
        .report-header h1 i {
            color: #4fc3f7;
        }
        
        .report-period {
            color: #b0c7e6;
            font-size: 15px;
            margin-bottom: 15px;
        }
        
        .summary-cards {
            display: flex;
            gap: 20px;
            margin-top: 25px;
        }
        
        .summary-card {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .summary-card.new {
            border-top: 4px solid #4caf50;
        }
        
        .summary-card.closed {
            border-top: 4px solid #f44336;
        }
        
        .card-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #b0c7e6;
            margin-bottom: 10px;
        }
        
        .card-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .report-content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #4a6491;
        }
        
        .status-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .status-item {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 18px;
            border-left: 4px solid #4a6491;
            transition: all 0.3s ease;
        }
        
        .status-item:hover {
            background-color: #edf2f7;
            transform: translateX(5px);
        }
        
        .status-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-count {
            font-size: 24px;
            font-weight: 700;
            color: #4a6491;
        }
        
        .export-options {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eaeaea;
        }
        
        .export-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 24px;
            background-color: #4a6491;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            max-width: 200px;
        }
        
        .export-btn:hover {
            background-color: #2c3e50;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .export-btn.pdf {
            background-color: #f44336;
        }
        
        .export-btn.csv {
            background-color: #4caf50;
        }
        
        .report-footer {
            text-align: center;
            padding: 20px;
            color: #7a7a7a;
            font-size: 14px;
            border-top: 1px solid #eaeaea;
            background-color: #f8f9fa;
        }
        
        @media (max-width: 768px) {
            .summary-cards {
                flex-direction: column;
            }
            
            .status-breakdown {
                grid-template-columns: 1fr;
            }
            
            .export-options {
                flex-direction: column;
                align-items: center;
            }
            
            .export-btn {
                max-width: 100%;
                width: 100%;
            }
        }
        
        /* Status color coding */
        .status-item.open { border-left-color: #2196f3; }
        .status-item.open .status-count { color: #2196f3; }
        
        .status-item.in-progress { border-left-color: #ff9800; }
        .status-item.in-progress .status-count { color: #ff9800; }
        
        .status-item.resolved { border-left-color: #4caf50; }
        .status-item.resolved .status-count { color: #4caf50; }
        
        .status-item.closed { border-left-color: #9e9e9e; }
        .status-item.closed .status-count { color: #9e9e9e; }
        
        .status-item.pending { border-left-color: #ff5722; }
        .status-item.pending .status-count { color: #ff5722; }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1 style="color:white"><i class="fas fa-chart-line"></i> Weekly QA/QC Report</h1>
            <div class="report-period">Week of June 10 - June 16, 2023</div>
            
            <div class="summary-cards">
                <div class="summary-card new">
                    <div class="card-title">New Items</div>
                    <div class="card-value">12</div>
                    <div class="card-change positive"><i class="fas fa-arrow-up"></i> 2 from last week</div>
                </div>
                
                <div class="summary-card closed">
                    <div class="card-title">Closed Items</div>
                    <div class="card-value">8</div>
                    <div class="card-change positive"><i class="fas fa-arrow-up"></i> 3 from last week</div>
                </div>
            </div>
        </div>
        
        <div class="report-content">
            <h2 class="section-title"><i class="fas fa-chart-pie"></i> Status Breakdown</h2>
            
            <div class="status-breakdown">
                <div class="status-item open">
                    <div class="status-name">
                        Open
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="status-count">5</div>
                </div>
                
                <div class="status-item in-progress">
                    <div class="status-name">
                        In Progress
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="status-count">9</div>
                </div>
                
                <div class="status-item resolved">
                    <div class="status-name">
                        Resolved
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-count">7</div>
                </div>
                
                <div class="status-item closed">
                    <div class="status-name">
                        Closed
                        <i class="fas fa-archive"></i>
                    </div>
                    <div class="status-count">8</div>
                </div>
                
                <div class="status-item pending">
                    <div class="status-name">
                        Pending Review
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="status-count">3</div>
                </div>
            </div>
            
            <div class="export-options">
                <a href="/reports/weekly/pdf" class="export-btn pdf">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                
                <a href="/reports/weekly/csv" class="export-btn csv">
                    <i class="fas fa-file-csv"></i> Download CSV
                </a>
            </div>
        </div>
        
        <div class="report-footer">
            Report generated on June 17, 2023 | QA/QC Dashboard v2.1
        </div>
    </div>

    <script>
        // Dynamic data update (simulating live data)
        document.addEventListener('DOMContentLoaded', function() {
            // This would typically come from your backend/API
            const reportData = {
                created_items: 12,
                closed_items: 8,
                by_status: {
                    open: 5,
                    "in-progress": 9,
                    resolved: 7,
                    closed: 8,
                    pending: 3
                }
            };
            
            // Update summary cards
            document.querySelector('.summary-card.new .card-value').textContent = reportData.created_items;
            document.querySelector('.summary-card.closed .card-value').textContent = reportData.closed_items;
            
            // Update status items
            const statusItems = document.querySelectorAll('.status-item');
            statusItems.forEach(item => {
                const statusName = item.querySelector('.status-name').textContent.toLowerCase().trim();
                let matchingKey = '';
                
                // Find matching key in report data
                if (statusName.includes('open')) matchingKey = 'open';
                else if (statusName.includes('progress')) matchingKey = 'in-progress';
                else if (statusName.includes('resolved')) matchingKey = 'resolved';
                else if (statusName.includes('closed')) matchingKey = 'closed';
                else if (statusName.includes('pending')) matchingKey = 'pending';
                
                if (reportData.by_status[matchingKey] !== undefined) {
                    item.querySelector('.status-count').textContent = reportData.by_status[matchingKey];
                }
            });
            
            // Add hover effect to cards
            const cards = document.querySelectorAll('.summary-card, .status-item, .export-btn');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
        });
    </script>


@endsection