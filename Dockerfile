# เลือก image ของ PHP ที่มี Apache
FROM php:7.4-apache

# กำหนด directory ที่จะเก็บไฟล์โปรเจกต์
WORKDIR /var/www/html

# คัดลอกไฟล์ทั้งหมดในโปรเจกต์ไปยัง container
COPY . .

# ติดตั้ง dependencies เช่น extension ที่ต้องใช้
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pgsql pdo_pgsql


# กำหนด port ที่ต้องการ
EXPOSE 80

# กำหนดคำสั่งในการเริ่มต้นเว็บเซิร์ฟเวอร์
CMD ["apache2-foreground"]




