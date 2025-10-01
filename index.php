<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NutriMate - Landingpage</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans text-gray-900 bg-white">

  <!-- ✅ Header -->
  <header class="fixed top-0 left-0 w-full bg-black shadow z-50">
    <div class="container mx-auto flex justify-between items-center py-4 px-6">
      <!-- Logo -->
      <div class="flex items-center space-x-2">
        <img src="aset/logo-removebg-preview.png" alt="NutriMate Logo" class="h-8">
        <p class="font-bold text-lg text-[#ffca28]">NUT<span class="text-white">RIMATE</span></p>
      </div>

      <!-- Navigation -->
      <nav class="hidden md:flex space-x-8 text-gray-200">
        <a href="#home" class="hover:text-[#ffca28]">Beranda</a>
        <a href="#features" class="hover:text-[#ffca28]">Layanan</a>
        <a href="#news" class="hover:text-[#ffca28]">Berita</a>
        <a href="#contact" class="hover:text-[#ffca28]">Kontak</a>
      </nav>

      <!-- Buttons -->
      <div class="md:flex space-x-4">
        <a href="public/login.php" class="font-bold bg-gradient-to-r from-[#ffca28] to-yellow-400 text-black px-4 py-2 rounded-lg shadow hover:from-yellow-500 hover:to-[#ffca28]">
          Masuk
        </a>
        <a href="public/register.php" class="font-bold bg-gradient-to-r from-[#ffca28] to-yellow-400 text-black px-4 py-2 rounded-lg shadow hover:from-yellow-500 hover:to-[#ffca28]">
          Daftar
        </a>
      </div>
    </div>
  </header>

  <!-- ✅ Hero Section -->
  <section id="home" class="pt-28 bg-white">
    <div class="container mx-auto flex flex-col md:flex-row items-center px-6">
      
      <!-- Left Content -->
      <div class="flex-1 space-y-6">
        <h1 class="text-4xl md:text-5xl font-bold leading-snug text-black">
          Teman Setia Menuju <br> Hidup Sehat
        </h1>
        <p class="text-gray-700 max-w-lg">
          Dengan NutriMate, kamu dapat melacak aktivitas harian, mendapatkan tips hidup sehat,
          dan berkomunikasi langsung lewat chatbot interaktif.
        </p>
        <a href="#features" class="font-bold inline-block bg-gradient-to-r from-[#ffca28] to-yellow-400 text-black px-6 py-3 rounded-lg shadow hover:from-yellow-500 hover:to-[#ffca28]">
          Get Started
        </a>
      </div>

      <!-- Right Image -->
      <div class="flex-1 mt-10 md:mt-0 flex justify-center">
        <img src="aset/Gemini_Generated_Image_lu74eglu74eglu74-removebg-preview.png" alt="Ilustrasi olahraga" class="max-w-sm md:max-w-md">
      </div>

    </div>
  </section>

  <!-- ✅ Features Section -->
  <section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
      <div class="text-left mb-12 md:flex md:justify-between md:items-start">
        <h2 class="text-3xl font-bold mb-4 md:mb-0 text-black">Solusi Hidup Sehat Anda.</h2>
        <p class="max-w-xl text-gray-600">
          NutriMate hadir dengan fitur lengkap seperti Tracking Aktivitas, Tips Hidup Sehat, 
          dan Chatbot Interaktif.
        </p>
      </div>

      <!-- Cards -->
      <div class="grid md:grid-cols-3 gap-8">
        <!-- Card 1 -->
        <div class="relative bg-gradient-to-r from-[#ffca28] to-yellow-400 p-6 rounded-xl shadow hover:shadow-lg transition">
          <div class="absolute -top-2 -left-2 bg-black text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold">1</div>
          <img src="aset/gambar1.png" alt="Tracking Aktivitas" class="rounded-md mb-4 w-full h-40 object-cover">
          <h3 class="text-lg font-semibold mb-2 text-white">Tracking Aktivitas</h3>
          <p class="text-gray-300 text-sm">
            Pantau dan catat kegiatan harian dengan mudah dan terstruktur.
          </p>
        </div>

        <!-- Card 2 -->
        <div class="relative bg-gradient-to-r from-[#ffca28] to-yellow-400 p-6 rounded-xl shadow hover:shadow-lg transition">
          <div class="absolute -top-2 -left-2 bg-black text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold">2</div>
          <img src="aset/gambar2.png" alt="Tips" class="rounded-md mb-4 w-full h-40 object-cover">
          <h3 class="text-lg font-semibold mb-2 text-white">Tips & Trick</h3>
          <p class="text-gray-300 text-sm">
            Saran praktis seputar pola hidup sehat yang mudah diterapkan.
          </p>
        </div>

        <!-- Card 3 -->
        <div class="relative bg-gradient-to-r from-[#ffca28] to-yellow-400 p-6 rounded-xl shadow hover:shadow-lg transition">
          <div class="absolute -top-2 -left-2 bg-black text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold">3</div>
          <img src="aset/gambar3.png" alt="Chat Bot" class="rounded-md mb-4 w-full h-40 object-cover">
          <h3 class="text-lg font-semibold mb-2 text-white">Chat Bot</h3>
          <p class="text-gray-300 text-sm">
            Dapatkan saran kesehatan secara cepat dan interaktif kapan pun.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ✅ News Section -->
  <section id="news" class="py-20 bg-white">
    <div class="container mx-auto px-6 text-center">
      <span class="bg-[#ffca28] text-black px-4 py-1 rounded-full text-sm font-medium">
        News
      </span>
      <h2 class="text-3xl font-bold mt-4 mb-12 text-black">Jaga Selalu Kesehatan Anda</h2>

      <!-- Cards -->
      <div class="grid md:grid-cols-3 gap-8">
        <!-- Card 1 -->
        <div class="bg-gray-50 rounded-xl shadow-lg overflow-hidden">
          <img src="aset/Frame 23.png" alt="Berita Kesehatan" class="w-full h-56 object-cover">
          <div class="p-6 text-left">
            <h3 class="text-lg font-semibold mb-2 text-black">Berita Kesehatan Terkini</h3>
            <p class="text-gray-600 text-sm mb-4">Statistik mengungkapkan: 70% orang Indonesia cari informasi kesehatan online...</p>
            <a href="https://health.kompas.com/" target="_blank" class="text-[#ffca28] font-medium hover:underline">Read More →</a>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-gray-50 rounded-xl shadow-lg overflow-hidden">
          <img src="aset/Frame 24.png" alt="Ahli Gizi" class="w-full h-56 object-cover">
          <div class="p-6 text-left">
            <h3 class="text-lg font-semibold mb-2 text-black">Ahli Gizi Ingatkan Nutrisi Lansia</h3>
            <p class="text-gray-600 text-sm mb-4">Dari total 221.000 jemaah, lebih dari 80% merupakan jemaah berisiko tinggi...</p>
            <a href="https://www.alodokter.com/" target="_blank" class="text-[#ffca28] font-medium hover:underline">Read More →</a>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-gray-50 rounded-xl shadow-lg overflow-hidden">
          <img src="aset/image 140.png" alt="Tren Kesehatan" class="w-full h-56 object-cover">
          <div class="p-6 text-left">
            <h3 class="text-lg font-semibold mb-2 text-black">Tren Kesehatan Gaya Hidup 2025</h3>
            <p class="text-gray-600 text-sm mb-4">Salah satu tren yang mencuri perhatian adalah diet berkelanjutan & pola makan sehat...</p>
            <a href="https://www.webmd.com/news/" target="_blank" class="text-[#ffca28] font-medium hover:underline">Read More →</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ✅ Footer -->
  <footer id="contact" class="mt-20">
    <!-- Top Footer -->
    <div class="bg-black text-white py-12">
      <div class="container mx-auto px-6 grid md:grid-cols-3 gap-8">
        <!-- Contact Info -->
        <div class="space-y-4">
          <p class="flex items-center gap-3"><span class="text-[#ffca28]"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16">
  <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
</svg></span> Sintech@gmail.com</p>
          <p class="flex items-center gap-3"><span class="text-[#ffca28]"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-x" viewBox="0 0 16 16">
  <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
  <path fill-rule="evenodd" d="M11.146 1.646a.5.5 0 0 1 .708 0L13 2.793l1.146-1.147a.5.5 0 0 1 .708.708L13.707 3.5l1.147 1.146a.5.5 0 0 1-.708.708L13 4.207l-1.146 1.147a.5.5 0 0 1-.708-.708L12.293 3.5l-1.147-1.146a.5.5 0 0 1 0-.708"/>
</svg></span> +012345678910</p>
          <p class="flex items-center gap-3"><span class="text-[#ffca28]"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
  <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
</svg></span> Kampus 3 UIN Walisongo</p>
        </div>

        <!-- Pages -->
        <div>
          <h4 class="font-bold mb-4 text-[#ffca28]">PAGES</h4>
          <ul class="space-y-2">
            <li><a href="#home" class="hover:text-[#ffca28]">Home Page</a></li>
            <li><a href="#features" class="hover:text-[#ffca28]">Service</a></li>
            <li><a href="#news" class="hover:text-[#ffca28]">News</a></li>
            <li><a href="#contact" class="hover:text-[#ffca28]">Contact</a></li>
          </ul>
        </div>

        <!-- Social Media -->
        <div>
          <h4 class="font-bold mb-4 text-[#ffca28]">SOCIAL MEDIA</h4>
          <ul class="space-y-2">
            <li><a href="#" class="hover:text-[#ffca28]">Twitter</a></li>
            <li><a href="#" class="hover:text-[#ffca28]">Instagram</a></li>
            <li><a href="#" class="hover:text-[#ffca28]">Facebook</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom Footer -->
    <div class="bg-black text-white py-4">
      <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
        <p class="text-sm">Copyright © 2025. All rights reserved.</p>
        <div class="flex gap-4 mt-4 md:mt-0 text-lg">
          <a href="#" class="hover:text-black"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
          <a href="#" class="hover:text-black"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
  <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
</svg></a>
          <a href="#" class="hover:text-black"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
  <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
</svg></a>
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
