<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Files Report</title>
    <style>
        body {
            font-size: 10pt;
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
        }

        .company-name {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 10pt;
            margin-bottom: 10px;
        }

        .date-printed {
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color:#A8D08D;
            padding: 8px;
            text-align: left;
            border: 1px solid #2c2c2c;
            font-weight: bold;
        }

        td {
            padding: 8px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="company-name">Lee Systems Technology Ventures, Inc.</div>
    <div class="report-title">User Files Report</div>
    <div class="date-printed">Date Printed: {{ now()->format('m/d/Y') }}</div>

    @if($userFiles && $userFiles->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Type</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Is Locked</th>
                    <th>Is Active</th>
                    <th>Login Counts</th>
                    <th>Last Login</th>
                    <th>Last Change Password</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userFiles as $file)
                    <tr>
                        <td>{{ $file->id ?? '—' }}</td>
                        <td>{{ $file->userType->name ?? '—' }}</td>
                        <td>{{ $file->username ?? '—' }}</td>
                        <td>{{ $file->name ?? '—' }}</td>
                        <td>{{ $file->email ?? '—' }}</td>
                        <td>{{ $file->is_locked ?? '—' }}</td>
                        <td>{{ $file->is_active ?? '—' }}</td>
                        <td>{{ $file->login_counts ?? '—' }}</td>
                        <td>{{ $file->last_login ?? '—' }}</td>
                        <td>{{ $file->last_change_password ?? 'Never Change Password' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No user files found</p>
    @endif
</body>
</html>
