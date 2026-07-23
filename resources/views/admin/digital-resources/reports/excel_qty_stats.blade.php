<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; }
        .website-link { font-size: 11pt; }
        .main-title { font-size: 16pt; font-weight: bold; text-align: center; margin-top: 15px; margin-bottom: 20px; }
        .folder-header { font-size: 12pt; font-weight: bold; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background-color: #d9e2f3; border: 1px solid #000000; font-weight: bold; text-align: center; font-size: 11pt; padding: 5px; }
        td { border: 1px solid #000000; font-size: 11pt; padding: 4px 6px; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="3" style="border: none; font-size: 11pt;">Website: http://library.vttu.edu.vn/</td>
        </tr>
        <tr><td colspan="3" style="border: none;"></td></tr>
        <tr>
            <td colspan="3" style="border: none; font-size: 16pt; font-weight: bold; text-align: center;">
                THỐNG KÊ SỐ LƯỢNG TÀI LIỆU SỐ TRONG THƯ VIỆN
            </td>
        </tr>
        <tr><td colspan="3" style="border: none;"></td></tr>

        @foreach($foldersData as $folderData)
        <tr>
            <td colspan="3" style="border: none; font-weight: bold; font-size: 12pt; padding-top: 10px;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $folderData['folder_id'] }}&nbsp;&nbsp;&nbsp;&nbsp;{{ $folderData['folder_name'] }}
            </td>
        </tr>
        <thead>
            <tr>
                <th style="width: 15%; background-color: #d9e2f3; border: 1px solid #000000; font-weight: bold; text-align: center;">STT</th>
                <th style="width: 60%; background-color: #d9e2f3; border: 1px solid #000000; font-weight: bold; text-align: center;">THỂ LOẠI TÀI LIỆU</th>
                <th style="width: 25%; background-color: #d9e2f3; border: 1px solid #000000; font-weight: bold; text-align: center;">SỐ LƯỢNG</th>
            </tr>
        </thead>
        <tbody>
            @forelse($folderData['types'] as $idx => $typeRow)
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
                <td style="border: 1px solid #000000; text-align: left;">{{ $typeRow['name'] }}</td>
                <td style="border: 1px solid #000000; text-align: right;">{{ number_format($typeRow['count']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="border: 1px solid #000000; text-align: center; color: #666666; font-style: italic;">
                    Chưa có tài liệu số nào trong thư mục này
                </td>
            </tr>
            @endforelse
            <tr>
                <td colspan="2" style="border: none; font-weight: bold; text-align: right;">Tổng số:</td>
                <td style="border: none; font-weight: bold; text-align: right;">{{ number_format($folderData['total']) }}</td>
            </tr>
            <tr><td colspan="3" style="border: none;"></td></tr>
        </tbody>
        @endforeach
    </table>
</body>
</html>
