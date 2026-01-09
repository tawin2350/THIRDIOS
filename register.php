<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - THIRDIOS</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card register-card">
            <div class="auth-header">
                <div class="logo-container">
                    <img src="images/logo.PNG" alt="THIRDIOS Logo" class="logo">
                    <h1>THIRDIOS</h1>
                </div>
                <p>ระบบวิเคราะห์รายรับรายจ่ายส่วนบุคคล</p>
            </div>

            <form id="registerForm" class="auth-form">
                <h2>สมัครสมาชิก</h2>
                
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user-circle"></i>
                        ชื่อ-นามสกุล
                    </label>
                    <input type="text" id="full_name" name="full_name" required placeholder="กรอกชื่อ-นามสกุล">
                </div>

                <div class="form-group">
                    <label for="reg_username">
                        <i class="fas fa-user"></i>
                        ชื่อผู้ใช้
                    </label>
                    <input type="text" id="reg_username" name="username" required placeholder="กรอกชื่อผู้ใช้ (อักษรภาษาอังกฤษหรือตัวเลข)">
                    <small>ชื่อผู้ใช้ต้องมีความยาว 4-20 ตัวอักษร</small>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        อีเมล
                    </label>
                    <input type="email" id="email" name="email" required placeholder="กรอกอีเมล">
                </div>

                <div class="form-group">
                    <label for="reg_password">
                        <i class="fas fa-lock"></i>
                        รหัสผ่าน
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="reg_password" name="password" required placeholder="กรอกรหัสผ่าน">
                        <button type="button" class="toggle-password" onclick="togglePassword('reg_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small>รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        ยืนยันรหัสผ่าน
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="กรอกรหัสผ่านอีกครั้ง">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="agree" required>
                        <span>ฉันยอมรับ <a href="#" onclick="openTermsModal(event)">เงื่อนไขการใช้งาน</a></span>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i>
                    สมัครสมาชิก
                </button>

                <div class="form-footer">
                    <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
                </div>
            </form>
        </div>

        <div class="background-animation">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>เงื่อนไขการใช้งาน THIRDIOS</h2>
                <button class="modal-close" onclick="closeTermsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="terms-section">
                    <h3>1. การยอมรับเงื่อนไข</h3>
                    <p>การใช้งานเว็บไซต์ THIRDIOS (ต่อไปนี้เรียกว่า "บริการ") ถือว่าคุณยอมรับและตกลงที่จะปฏิบัติตามเงื่อนไขการใช้งานนี้ทุกประการ หากคุณไม่เห็นด้วยกับเงื่อนไขใดๆ กรุณาหยุดการใช้งานบริการทันที</p>
                </div>

                <div class="terms-section">
                    <h3>2. การให้บริการ</h3>
                    <p>THIRDIOS เป็นระบบจัดการการเงินส่วนบุคคลที่ให้บริการ:</p>
                    <ul>
                        <li>บันทึกและติดตามรายรับ-รายจ่ายส่วนบุคคล</li>
                        <li>วิเคราะห์กระแสเงินสดผ่านกราฟและรายงาน</li>
                        <li>จัดหมวดหมู่รายการทางการเงิน</li>
                        <li>สรุปภาพรวมการเงินรายเดือนและรายปี</li>
                    </ul>
                    <p>เราขอสงวนสิทธิ์ในการเปลี่ยนแปลง ปรับปรุง หรือยกเลิกบริการใดๆ โดยไม่ต้องแจ้งให้ทราบล่วงหน้า</p>
                </div>

                <div class="terms-section">
                    <h3>3. บัญชีผู้ใช้งาน</h3>
                    <ul>
                        <li>คุณต้องให้ข้อมูลที่ถูกต้องและเป็นจริงเมื่อสมัครสมาชิก</li>
                        <li>คุณมีหน้าที่รับผิดชอบในการรักษาความปลอดภัยของรหัสผ่าน</li>
                        <li>ห้ามแชร์บัญชีผู้ใช้หรือรหัสผ่านให้บุคคลอื่น</li>
                        <li>คุณต้องแจ้งให้เราทราบทันทีหากพบการใช้งานบัญชีโดยไม่ได้รับอนุญาต</li>
                        <li>ห้ามสร้างบัญชีที่มีชื่อหยาบคาย ไม่เหมาะสม หรือละเมิดสิทธิ์ผู้อื่น</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>4. ความเป็นส่วนตัวและข้อมูล</h3>
                    <ul>
                        <li>เราเก็บรักษาข้อมูลส่วนบุคคลของคุณอย่างปลอดภัย</li>
                        <li>ข้อมูลการเงินของคุณจะไม่ถูกเปิดเผยต่อบุคคลที่สาม</li>
                        <li>เราใช้ข้อมูลเพื่อปรับปรุงบริการและประสบการณ์ผู้ใช้</li>
                        <li>คุณสามารถขอลบข้อมูลของคุณได้ตามกฎหมายคุ้มครองข้อมูลส่วนบุคคล</li>
                        <li>ผู้ดูแลระบบสามารถเข้าถึงข้อมูลเพื่อการจัดการและแก้ไขปัญหาเท่านั้น</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>5. การใช้งานที่ยอมรับได้</h3>
                    <p>ห้ามใช้บริการเพื่อ:</p>
                    <ul>
                        <li>กิจกรรมที่ผิดกฎหมายหรือฉ้อโกง</li>
                        <li>แชร์ข้อมูลที่เป็นเท็จ หลอกลวง หรือทำให้เข้าใจผิด</li>
                        <li>รบกวนหรือขัดขวางการทำงานของระบบ</li>
                        <li>พยายามเข้าถึงข้อมูลของผู้ใช้อื่นโดยไม่ได้รับอนุญาต</li>
                        <li>ใช้ระบบอัตโนมัติ (bot) หรือวิธีการอื่นที่ไม่เหมาะสม</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>6. ความรับผิดชอบของผู้ใช้</h3>
                    <ul>
                        <li>คุณรับผิดชอบในการตรวจสอบความถูกต้องของข้อมูลที่บันทึก</li>
                        <li>เราไม่รับผิดชอบต่อความเสียหายที่เกิดจากข้อมูลที่ไม่ถูกต้อง</li>
                        <li>คุณต้องสำรองข้อมูลสำคัญของคุณเอง</li>
                        <li>การตัดสินใจทางการเงินเป็นความรับผิดชอบของคุณเอง</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>7. ข้อจำกัดความรับผิด</h3>
                    <ul>
                        <li>บริการให้บริการ "ตามสภาพที่เป็นอยู่" โดยไม่มีการรับประกันใดๆ</li>
                        <li>เราไม่รับประกันว่าบริการจะปราศจากข้อผิดพลาดหรือไม่หยุดชะงัก</li>
                        <li>เราไม่รับผิดชอบต่อความเสียหายโดยตรง โดยอ้อม หรือที่เกิดขึ้นตามมา</li>
                        <li>คุณใช้บริการโดยยอมรับความเสี่ยงของคุณเอง</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>8. การระงับและยกเลิกบัญชี</h3>
                    <p>เราสงวนสิทธิ์ในการระงับหรือยกเลิกบัญชีของคุณได้ทันทีหาก:</p>
                    <ul>
                        <li>คุณละเมิดเงื่อนไขการใช้งาน</li>
                        <li>มีการใช้งานที่ผิดปกติหรือน่าสงสัย</li>
                        <li>ไม่มีการใช้งานเป็นระยะเวลานาน (มากกว่า 1 ปี)</li>
                        <li>ได้รับคำร้องขอจากหน่วยงานที่มีอำนาจ</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h3>9. การเปลี่ยนแปลงเงื่อนไข</h3>
                    <p>เราอาจปรับปรุงเงื่อนไขการใช้งานนี้เป็นครั้งคราว การใช้งานบริการต่อไปถือว่าคุณยอมรับเงื่อนไขที่เปลี่ยนแปลง เราจะแจ้งให้ทราบผ่านระบบประกาศหากมีการเปลี่ยนแปลงสำคัญ</p>
                </div>

                <div class="terms-section">
                    <h3>10. กฎหมายที่ใช้บังคับ</h3>
                    <p>เงื่อนไขการใช้งานนี้อยู่ภายใต้กฎหมายไทย ข้อพิพาทใดๆ จะอยู่ในเขตอำนาจศาลไทย</p>
                </div>

                <div class="terms-section">
                    <h3>11. การติดต่อ</h3>
                    <p>หากมีข้อสงสัยเกี่ยวกับเงื่อนไขการใช้งาน กรุณาติดต่อ:</p>
                    <ul>
                        <li>อีเมล: support@thirdios.com</li>
                        <li>โทรศัพท์: 0956264519</li>
                    </ul>
                </div>

                <div class="terms-footer">
                    <p><strong>วันที่มีผลบังคับใช้:</strong> 9 มกราคม 2569</p>
                    <p><strong>เวอร์ชัน:</strong> 1.0.0</p>
                    <p><em>กรุณาอ่านและทำความเข้าใจเงื่อนไขทั้งหมดก่อนใช้งานบริการ</em></p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-accept" onclick="acceptTerms()">
                    <i class="fas fa-check"></i> ยอมรับเงื่อนไข
                </button>
                <button class="btn-decline" onclick="closeTermsModal()">
                    <i class="fas fa-times"></i> ปิด
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script src="script/auth.js"></script>
</body>
</html>
