<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP SapiSehat</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #2E7D32;">SapiSehat</h2>
        <p>Aplikasi Deteksi Dini Penyakit Sapi</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 8px; border-top: 4px solid #4CAF50;">
        <h3 style="margin-top: 0;">Halo!</h3>
        <p>Kami menerima permintaan untuk mengatur ulang kata sandi (Reset Password) untuk akun SapiSehat Anda.</p>
        
        <p>Berikut adalah kode OTP Anda. Masukkan kode ini di aplikasi SapiSehat untuk melanjutkan proses pergantian kata sandi:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1565C0; background-color: #E3F2FD; padding: 15px 30px; border-radius: 6px;">
                {{ $otp }}
            </span>
        </div>
        
        <p style="color: #d32f2f; font-size: 14px;"><strong>Peringatan:</strong> Kode ini hanya berlaku selama 15 menit. Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak yang mengatasnamakan SapiSehat.</p>
        
        <p>Jika Anda tidak merasa meminta perubahan kata sandi, abaikan email ini. Akun Anda tetap aman.</p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #777;">
        <p>&copy; {{ date('Y') }} SapiSehat. Hak Cipta Dilindungi.</p>
    </div>
</body>
</html>
