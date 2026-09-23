<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SwiftShare - Comparte archivos grandes de forma rápida, segura y gratuita desde cualquier dispositivo." />
  <meta name="theme-color" content="#0a0a0f" />
  <title>SwiftShare — Comparte sin límites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet" />
  <style>
    :root {
      --clr-bg:#08080e; --clr-surface:#111118; --clr-card:#16161f;
      --clr-border:rgba(255,255,255,0.07); --clr-accent:#6c63ff;
      --clr-accent2:#ff6584; --clr-accent3:#43e97b;
      --clr-text:#e8e8f0; --clr-muted:#7a7a96; --clr-white:#ffffff;
      --font-display:'Syne',sans-serif; --font-body:'DM Sans',sans-serif;
      --radius-sm:8px; --radius-md:14px; --radius-lg:24px; --radius-xl:36px;
      --shadow-glow:0 0 60px rgba(108,99,255,0.18);
      --transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{background:var(--clr-bg);color:var(--clr-text);font-family:var(--font-body);font-weight:400;line-height:1.6;overflow-x:hidden;}
    h1,h2,h3,h4,h5,h6{font-family:var(--font-display);font-weight:700;}
    a{color:var(--clr-accent);text-decoration:none;transition:var(--transition);}
    a:hover{color:var(--clr-accent2);}
    ::selection{background:var(--clr-accent);color:#fff;}
    ::-webkit-scrollbar{width:6px;}
    ::-webkit-scrollbar-track{background:var(--clr-bg);}
    ::-webkit-scrollbar-thumb{background:var(--clr-accent);border-radius:4px;}

    /* BG ORBS */
    .bg-orbs{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden;}
    .orb{position:absolute;border-radius:50%;filter:blur(100px);opacity:.12;animation:orbFloat 12s ease-in-out infinite alternate;}
    .orb-1{width:600px;height:600px;background:var(--clr-accent);top:-200px;left:-200px;}
    .orb-2{width:400px;height:400px;background:var(--clr-accent2);bottom:-100px;right:-100px;animation-delay:-4s;}
    .orb-3{width:300px;height:300px;background:var(--clr-accent3);top:40%;left:50%;animation-delay:-8s;}
    @keyframes orbFloat{0%{transform:translate(0,0) scale(1);}100%{transform:translate(40px,40px) scale(1.1);}}

    /* NAVBAR */
    .navbar{background:rgba(8,8,14,.75);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--clr-border);padding:1rem 0;position:sticky;top:0;z-index:1000;}
    .navbar-brand{font-family:var(--font-display);font-weight:800;font-size:1.5rem;color:var(--clr-white)!important;letter-spacing:-.5px;}
    .navbar-brand span{color:var(--clr-accent);}
    .logo-icon{width:36px;height:36px;background:var(--clr-accent);border-radius:10px;display:inline-flex;align-items:center;justify-content:center;margin-right:8px;font-size:1rem;color:#fff;}
    .nav-link{color:var(--clr-muted)!important;font-weight:500;font-size:.9rem;padding:.4rem .9rem!important;border-radius:var(--radius-sm);transition:var(--transition);}
    .nav-link:hover,.nav-link.active{color:var(--clr-white)!important;background:rgba(255,255,255,.06);}
    .navbar-toggler{border:1px solid var(--clr-border);color:var(--clr-text);padding:6px 10px;}
    .navbar-toggler:focus{box-shadow:0 0 0 2px var(--clr-accent);}
    .navbar-toggler-icon{filter:invert(1);}
    .btn-nav-login{background:transparent;border:1px solid var(--clr-border);color:var(--clr-text)!important;padding:.4rem 1.1rem!important;border-radius:var(--radius-sm);font-size:.875rem;}
    .btn-nav-login:hover{border-color:var(--clr-accent);color:var(--clr-accent)!important;}
    .btn-nav-cta{background:var(--clr-accent);border:none;color:#fff!important;padding:.45rem 1.2rem!important;border-radius:var(--radius-sm);font-size:.875rem;font-weight:600;}
    .btn-nav-cta:hover{background:#5a52e0;color:#fff!important;}
    .dropdown-menu{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-md);box-shadow:0 20px 60px rgba(0,0,0,.5);padding:.5rem;margin-top:8px!important;}
    .dropdown-item{color:var(--clr-muted);font-size:.875rem;border-radius:var(--radius-sm);padding:.5rem .9rem;transition:var(--transition);}
    .dropdown-item:hover{background:rgba(255,255,255,.06);color:var(--clr-white);}
    .dropdown-divider{border-color:var(--clr-border);}

    /* HERO */
    .hero{position:relative;z-index:1;padding:5rem 0 4rem;min-height:calc(100vh - 70px);display:flex;align-items:center;}
    .hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(108,99,255,.1);border:1px solid rgba(108,99,255,.3);color:var(--clr-accent);font-size:.8rem;font-weight:600;padding:.35rem .9rem;border-radius:100px;margin-bottom:1.5rem;animation:fadeInUp .6s ease both;}
    .dot{width:6px;height:6px;background:var(--clr-accent3);border-radius:50%;animation:pulse 2s infinite;}
    @keyframes pulse{0%,100%{opacity:1;}50%{opacity:.3;}}
    .hero-title{font-size:clamp(2.8rem,6vw,5.5rem);font-weight:800;line-height:1.05;letter-spacing:-2px;color:var(--clr-white);animation:fadeInUp .6s .1s ease both;}
    .highlight{background:linear-gradient(135deg,var(--clr-accent),var(--clr-accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .hero-subtitle{font-size:clamp(1rem,2vw,1.2rem);color:var(--clr-muted);max-width:500px;margin:1.5rem 0 2.5rem;font-weight:300;line-height:1.8;animation:fadeInUp .6s .2s ease both;}
    .hero-actions{animation:fadeInUp .6s .3s ease both;}
    .btn-primary-main{background:var(--clr-accent);border:none;color:#fff;padding:.85rem 2rem;font-size:1rem;font-weight:600;border-radius:var(--radius-md);transition:var(--transition);box-shadow:0 8px 30px rgba(108,99,255,.35);display:inline-block;}
    .btn-primary-main:hover{background:#5a52e0;color:#fff;transform:translateY(-2px);box-shadow:0 12px 40px rgba(108,99,255,.5);}
    .btn-outline-main{background:transparent;border:1px solid var(--clr-border);color:var(--clr-text);padding:.85rem 2rem;font-size:1rem;font-weight:500;border-radius:var(--radius-md);transition:var(--transition);display:inline-block;}
    .btn-outline-main:hover{border-color:var(--clr-accent);color:var(--clr-accent);background:rgba(108,99,255,.05);}
    .hero-stats{display:flex;gap:2rem;flex-wrap:wrap;margin-top:3rem;animation:fadeInUp .6s .4s ease both;}
    .stat-value{font-family:var(--font-display);font-size:1.8rem;font-weight:800;color:var(--clr-white);line-height:1;}
    .stat-label{font-size:.8rem;color:var(--clr-muted);margin-top:4px;}

    /* UPLOAD CARD */
    .upload-card{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-xl);padding:2rem;position:relative;overflow:hidden;animation:fadeInRight .8s .3s ease both;box-shadow:var(--shadow-glow);}
    .upload-card::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at top right,rgba(108,99,255,.1),transparent 60%);pointer-events:none;}
    .dropzone{border:2px dashed rgba(108,99,255,.4);border-radius:var(--radius-lg);padding:2.5rem 1.5rem;text-align:center;cursor:pointer;transition:var(--transition);background:rgba(108,99,255,.03);}
    .dropzone:hover,.dropzone.drag-over{border-color:var(--clr-accent);background:rgba(108,99,255,.08);}
    .dropzone-icon{width:64px;height:64px;background:rgba(108,99,255,.12);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--clr-accent);margin:0 auto 1rem;}
    .dropzone-title{font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--clr-white);}
    .dropzone-sub{font-size:.8rem;color:var(--clr-muted);margin-top:4px;}
    .dropzone-limit{display:inline-block;margin-top:.75rem;background:rgba(255,255,255,.05);border-radius:100px;font-size:.75rem;color:var(--clr-muted);padding:3px 12px;}
    .file-list{margin-top:1rem;display:none;}
    .file-item{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.04);border-radius:var(--radius-sm);padding:.6rem .9rem;margin-bottom:6px;}
    .file-icon{font-size:1.2rem;color:var(--clr-accent);}
    .file-name{font-size:.825rem;color:var(--clr-text);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .file-size{font-size:.75rem;color:var(--clr-muted);}
    .file-remove{color:var(--clr-muted);cursor:pointer;font-size:.9rem;}
    .file-remove:hover{color:var(--clr-accent2);}
    .upload-form{margin-top:1.25rem;}
    .form-label-custom{font-size:.78rem;font-weight:600;color:var(--clr-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem;display:block;}
    .form-control-custom{background:rgba(255,255,255,.05);border:1px solid var(--clr-border);color:var(--clr-text);border-radius:var(--radius-sm);padding:.65rem 1rem;font-size:.875rem;font-family:var(--font-body);width:100%;transition:var(--transition);outline:none;}
    .form-control-custom:focus{border-color:var(--clr-accent);background:rgba(108,99,255,.06);box-shadow:0 0 0 3px rgba(108,99,255,.15);}
    .form-control-custom::placeholder{color:var(--clr-muted);}
    .progress-wrap{display:none;margin-top:1rem;}
    .progress-label{display:flex;justify-content:space-between;font-size:.75rem;color:var(--clr-muted);margin-bottom:6px;}
    .progress-bar-track{height:4px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;}
    .progress-bar-fill{height:100%;width:0;background:linear-gradient(90deg,var(--clr-accent),var(--clr-accent2));border-radius:4px;transition:width .3s ease;}
    .transfer-tabs{display:flex;gap:6px;background:rgba(255,255,255,.04);border-radius:var(--radius-sm);padding:4px;margin-bottom:1rem;}
    .tab-btn{flex:1;padding:.5rem;background:transparent;border:none;color:var(--clr-muted);font-size:.8rem;font-weight:600;border-radius:6px;cursor:pointer;transition:var(--transition);font-family:var(--font-body);}
    .tab-btn.active{background:var(--clr-accent);color:#fff;}
    .form-select-custom{background:rgba(255,255,255,.05) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%237a7a96' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 14px center;border:1px solid var(--clr-border);color:var(--clr-text);border-radius:var(--radius-sm);padding:.65rem 2.5rem .65rem 1rem;font-size:.875rem;font-family:var(--font-body);width:100%;appearance:none;-webkit-appearance:none;outline:none;transition:var(--transition);}
    .form-select-custom:focus{border-color:var(--clr-accent);background-color:rgba(108,99,255,.06);}
    .form-select-custom option{background:var(--clr-card);}

    /* SECTIONS */
    .section{position:relative;z-index:1;padding:5rem 0;}
    .section-label{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--clr-accent);margin-bottom:1rem;}
    .section-title{font-size:clamp(2rem,4vw,3rem);font-weight:800;letter-spacing:-1px;color:var(--clr-white);line-height:1.1;}
    .section-subtitle{font-size:1rem;color:var(--clr-muted);max-width:500px;margin:1rem 0 0;font-weight:300;line-height:1.8;}

    /* FEATURE CARDS */
    .feature-card{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:2rem;height:100%;transition:var(--transition);position:relative;overflow:hidden;}
    .feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--clr-accent),transparent);opacity:0;transition:var(--transition);}
    .feature-card:hover{border-color:rgba(108,99,255,.3);transform:translateY(-4px);box-shadow:var(--shadow-glow);}
    .feature-card:hover::before{opacity:1;}
    .feature-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1.25rem;}
    .icon-purple{background:rgba(108,99,255,.15);color:var(--clr-accent);}
    .icon-pink{background:rgba(255,101,132,.15);color:var(--clr-accent2);}
    .icon-green{background:rgba(67,233,123,.15);color:var(--clr-accent3);}
    .icon-orange{background:rgba(255,180,50,.15);color:#ffb432;}
    .icon-blue{background:rgba(50,200,255,.15);color:#32c8ff;}
    .icon-red{background:rgba(255,80,80,.15);color:#ff5050;}
    .feature-title{font-size:1.05rem;font-weight:700;color:var(--clr-white);margin-bottom:.5rem;}
    .feature-desc{font-size:.875rem;color:var(--clr-muted);line-height:1.7;}

    /* STEPS */
    .steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;}
    .step-card{text-align:center;padding:2rem 1rem;}
    .step-number{font-family:var(--font-display);font-size:4rem;font-weight:800;color:transparent;-webkit-text-stroke:1px rgba(108,99,255,.25);line-height:1;margin-bottom:1rem;}
    .step-icon{width:60px;height:60px;background:var(--clr-card);border:1px solid var(--clr-border);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--clr-accent);margin:0 auto 1.25rem;transition:var(--transition);}
    .step-card:hover .step-icon{background:var(--clr-accent);color:#fff;box-shadow:0 8px 30px rgba(108,99,255,.4);}
    .step-title{font-size:1rem;font-weight:700;color:var(--clr-white);margin-bottom:.5rem;}
    .step-desc{font-size:.85rem;color:var(--clr-muted);line-height:1.7;}

    /* PRICING */
    .plan-card{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-xl);padding:2.5rem 2rem;height:100%;transition:var(--transition);position:relative;}
    .plan-card.featured{border-color:var(--clr-accent);background:linear-gradient(135deg,rgba(108,99,255,.1),rgba(255,101,132,.06));box-shadow:0 0 50px rgba(108,99,255,.2);}
    .plan-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--clr-accent),var(--clr-accent2));color:#fff;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:4px 16px;border-radius:100px;white-space:nowrap;}
    .plan-name{font-size:1rem;font-weight:700;color:var(--clr-muted);text-transform:uppercase;letter-spacing:1px;}
    .plan-price{font-family:var(--font-display);font-size:3rem;font-weight:800;color:var(--clr-white);line-height:1;margin:.75rem 0;}
    .plan-price sup{font-size:1.2rem;vertical-align:super;}
    .plan-price small{font-size:1rem;color:var(--clr-muted);font-weight:400;}
    .plan-desc{font-size:.85rem;color:var(--clr-muted);margin-bottom:1.5rem;}
    .plan-feature{display:flex;align-items:center;gap:10px;font-size:.875rem;color:var(--clr-text);padding:.5rem 0;border-bottom:1px solid var(--clr-border);}
    .plan-feature:last-of-type{border:none;}
    .plan-feature .bi-check-circle-fill{color:var(--clr-accent3);}
    .plan-feature .bi-x-circle{color:var(--clr-muted);}
    .btn-plan{width:100%;margin-top:1.5rem;padding:.8rem;border-radius:var(--radius-md);font-weight:600;font-size:.9rem;transition:var(--transition);border:none;cursor:pointer;}
    .btn-plan-outline{background:transparent;border:1px solid var(--clr-border)!important;color:var(--clr-text);}
    .btn-plan-outline:hover{border-color:var(--clr-accent)!important;color:var(--clr-accent);}
    .btn-plan-filled{background:var(--clr-accent);color:#fff;box-shadow:0 8px 30px rgba(108,99,255,.4);}
    .btn-plan-filled:hover{background:#5a52e0;color:#fff;transform:translateY(-2px);}

    /* TESTIMONIALS */
    .testimonial-card{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-lg);padding:2rem;transition:var(--transition);height:100%;}
    .testimonial-card:hover{border-color:rgba(108,99,255,.25);}
    .stars{color:#ffb432;font-size:.85rem;letter-spacing:2px;margin-bottom:1rem;}
    .testimonial-text{font-size:.9rem;color:var(--clr-text);line-height:1.8;font-style:italic;margin-bottom:1.5rem;}
    .testimonial-author{display:flex;align-items:center;gap:12px;}
    .avatar{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:1rem;color:#fff;flex-shrink:0;}
    .av-1{background:linear-gradient(135deg,#6c63ff,#a78bfa);}
    .av-2{background:linear-gradient(135deg,#ff6584,#f472b6);}
    .av-3{background:linear-gradient(135deg,#43e97b,#38f9d7);color:#0a0a0f;}
    .av-4{background:linear-gradient(135deg,#f59e0b,#fcd34d);color:#0a0a0f;}
    .author-name{font-size:.875rem;font-weight:700;color:var(--clr-white);}
    .author-role{font-size:.775rem;color:var(--clr-muted);}

    /* FAQ */
    .accordion-item{background:var(--clr-card);border:1px solid var(--clr-border)!important;border-radius:var(--radius-md)!important;margin-bottom:.75rem;overflow:hidden;}
    .accordion-button{background:transparent;color:var(--clr-text);font-family:var(--font-body);font-weight:600;font-size:.9rem;padding:1.1rem 1.5rem;box-shadow:none!important;}
    .accordion-button:not(.collapsed){background:rgba(108,99,255,.06);color:var(--clr-white);}
    .accordion-button::after{filter:invert(1) brightness(.6);}
    .accordion-button:not(.collapsed)::after{filter:invert(.5) sepia(1) saturate(5) hue-rotate(220deg);}
    .accordion-body{background:transparent;color:var(--clr-muted);font-size:.875rem;line-height:1.8;padding:0 1.5rem 1.25rem;}

    /* CONTACT */
    .contact-card{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-xl);padding:3rem;position:relative;overflow:hidden;}
    .contact-card::after{content:'';position:absolute;bottom:-80px;right:-80px;width:300px;height:300px;background:radial-gradient(circle,rgba(108,99,255,.12),transparent 70%);pointer-events:none;}
    .form-floating-custom{position:relative;}
    .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--clr-muted);font-size:1rem;pointer-events:none;z-index:2;transition:var(--transition);}
    .form-control-custom.has-icon{padding-left:2.75rem;}
    .invalid-feedback{font-size:.75rem;color:var(--clr-accent2);}
    .form-check-input{background-color:rgba(255,255,255,.1);border:1px solid var(--clr-border);width:1rem;height:1rem;}
    .form-check-input:checked{background-color:var(--clr-accent);border-color:var(--clr-accent);}
    .form-check-label{font-size:.85rem;color:var(--clr-muted);}
    .divider-line{height:1px;background:var(--clr-border);margin:2rem 0;}

    /* CTA */
    .cta-banner{background:linear-gradient(135deg,rgba(108,99,255,.15),rgba(255,101,132,.1));border:1px solid rgba(108,99,255,.25);border-radius:var(--radius-xl);padding:4rem 3rem;text-align:center;position:relative;overflow:hidden;}
    .cta-banner::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at center,rgba(108,99,255,.12),transparent 70%);}
    .cta-title{font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:var(--clr-white);letter-spacing:-1px;}
    .cta-sub{font-size:1rem;color:var(--clr-muted);margin:1rem 0 2rem;}

    /* FOOTER */
    footer{background:var(--clr-surface);border-top:1px solid var(--clr-border);padding:4rem 0 2rem;position:relative;z-index:1;}
    .footer-brand{font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:var(--clr-white);margin-bottom:.75rem;}
    .footer-brand span{color:var(--clr-accent);}
    .footer-desc{font-size:.875rem;color:var(--clr-muted);line-height:1.7;max-width:260px;}
    .footer-socials{display:flex;gap:10px;margin-top:1.5rem;}
    .social-btn{width:38px;height:38px;background:rgba(255,255,255,.05);border:1px solid var(--clr-border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--clr-muted);font-size:1rem;transition:var(--transition);}
    .social-btn:hover{background:var(--clr-accent);border-color:var(--clr-accent);color:#fff;}
    .footer-heading{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--clr-white);margin-bottom:1rem;}
    .footer-links{list-style:none;padding:0;}
    .footer-links li{margin-bottom:.5rem;}
    .footer-links a{font-size:.875rem;color:var(--clr-muted);transition:var(--transition);}
    .footer-links a:hover{color:var(--clr-white);padding-left:4px;}
    .footer-divider{border-color:var(--clr-border);margin:2.5rem 0 1.5rem;}
    .footer-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
    .footer-copy{font-size:.8rem;color:var(--clr-muted);}
    .footer-legal{display:flex;gap:1.5rem;}
    .footer-legal a{font-size:.8rem;color:var(--clr-muted);}
    .footer-legal a:hover{color:var(--clr-white);}
    .newsletter-input-wrap{display:flex;gap:8px;}
    .newsletter-input-wrap input{flex:1;}
    .btn-newsletter{background:var(--clr-accent);border:none;color:#fff;padding:.65rem 1.2rem;border-radius:var(--radius-sm);font-size:.85rem;font-weight:600;white-space:nowrap;cursor:pointer;transition:var(--transition);}
    .btn-newsletter:hover{background:#5a52e0;}

    /* TOAST */
    .toast-container{position:fixed;bottom:2rem;right:2rem;z-index:9999;}
    .custom-toast{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-md);padding:1rem 1.25rem;color:var(--clr-text);font-size:.875rem;display:flex;align-items:center;gap:12px;box-shadow:0 20px 60px rgba(0,0,0,.4);min-width:280px;margin-top:8px;}
    .toast-icon{font-size:1.25rem;}

    /* MODAL */
    .modal-content{background:var(--clr-card);border:1px solid var(--clr-border);border-radius:var(--radius-xl);color:var(--clr-text);}
    .modal-header{border-bottom:1px solid var(--clr-border);padding:1.5rem 2rem;}
    .modal-title{font-family:var(--font-display);font-weight:700;color:var(--clr-white);}
    .btn-close{filter:invert(1) brightness(.6);}
    .modal-body{padding:2rem;}
    .modal-footer{border-top:1px solid var(--clr-border);padding:1rem 2rem;}

    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    @keyframes fadeInRight{from{opacity:0;transform:translateX(30px);}to{opacity:1;transform:translateX(0);}}

    /* ============================
       RESPONSIVE DESIGN BREAKPOINTS
       Documentado — Bootstrap 5 Grid
    ============================ */

    /* XL ≥1200px: Desktop grande */
    @media(min-width:1200px){
      .hero{padding:6rem 0 5rem;}
    }
    /* LG ≥992px: Desktop/Laptop */
    @media(min-width:992px){
      .hero-col-left{padding-right:3rem;}
    }
    /* MD ≥768px: Tablet landscape */
    @media(min-width:768px){
      .hero-stats{gap:3rem;}
    }
    /* SM <768px: Tablet portrait / Mobile grande */
    @media(max-width:767.98px){
      .hero{padding:3.5rem 0;min-height:auto;}
      .hero-title{letter-spacing:-1px;}
      .hero-subtitle{margin:1rem 0 1.75rem;}
      .hero-stats{gap:1.5rem;}
      .upload-card{margin-top:2.5rem;}
      .cta-banner{padding:2.5rem 1.5rem;}
      .contact-card{padding:2rem 1.25rem;}
      .footer-bottom{flex-direction:column;align-items:flex-start;}
      .newsletter-input-wrap{flex-direction:column;}
      .newsletter-input-wrap input,.btn-newsletter{width:100%;}
      .transfer-tabs{flex-wrap:wrap;}
    }
    /* XS <576px: Mobile pequeño */
    @media(max-width:575.98px){
      .hero-title{letter-spacing:-.5px;}
      .hero-actions{display:flex;flex-direction:column;gap:10px;}
      .btn-primary-main,.btn-outline-main{width:100%;text-align:center;}
      .steps-grid{grid-template-columns:1fr;gap:1.5rem;}
      .plan-card{padding:2rem 1.25rem;}
      .footer-legal{flex-direction:column;gap:.5rem;}
    }
  </style>
</head>
<body>

<div class="bg-orbs">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- ==================== NAVBAR ==================== -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="#">
    Big Data Transfer
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
      aria-controls="mainNav" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto gap-1">
        <li class="nav-item"><a class="nav-link active" href="#hero">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="#features">Características</a></li>
        <li class="nav-item"><a class="nav-link" href="#how-it-works">Cómo funciona</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Planes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#pricing"><i class="bi bi-gift me-2"></i>Gratis</a></li>
            <li><a class="dropdown-item" href="#pricing"><i class="bi bi-lightning-fill me-2"></i>Pro</a></li>
            <li><a class="dropdown-item" href="#pricing"><i class="bi bi-building me-2"></i>Business</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#pricing"><i class="bi bi-grid me-2"></i>Comparar planes</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
        <a href="#" class="nav-link btn-nav-login" data-bs-toggle="modal" data-bs-target="#loginModal">Iniciar sesión</a>
        <a href="#" class="nav-link btn-nav-cta" data-bs-toggle="modal" data-bs-target="#registerModal">Registrarse gratis</a>
      </div>
    </div>
  </div>
</nav>

<!-- ==================== HERO ==================== -->
<section class="hero" id="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 hero-col-left">
  
        <h1 class="hero-title">Comparte archivos<br><span class="highlight">sin límites</span></h1>
        <p class="hero-subtitle">Transfiere archivos de hasta 20 GB de forma instantánea, segura y desde cualquier dispositivo. Sin instalar nada.</p>
        <div class="hero-actions d-flex flex-wrap gap-3">
          <a href="#upload-zone" class="btn-primary-main"><i class="bi bi-cloud-upload me-2"></i>Enviar archivos</a>
          <a href="#how-it-works" class="btn-outline-main"><i class="bi bi-play-circle me-2"></i>Ver cómo funciona</a>
        </div>
        <div class="hero-stats">
          <div class="stat-item"><div class="stat-value">12M+</div><div class="stat-label">Usuarios activos</div></div>
          <div class="stat-item"><div class="stat-value">5PB</div><div class="stat-label">Datos transferidos</div></div>
          <div class="stat-item"><div class="stat-value">99.9%</div><div class="stat-label">Uptime garantizado</div></div>
        </div>
      </div>
      <div class="col-lg-6 mt-5 mt-lg-0" id="upload-zone">
        <div class="upload-card">
          <div class="transfer-tabs">
            <button class="tab-btn active" onclick="switchTab(this,'email')"><i class="bi bi-envelope me-1"></i>Por email</button>
            <button class="tab-btn" onclick="switchTab(this,'link')"><i class="bi bi-link-45deg me-1"></i>Enlace</button>
            <button class="tab-btn" onclick="switchTab(this,'request')"><i class="bi bi-inbox me-1"></i>Solicitar</button>
          </div>
          <div class="dropzone" id="dropzone"
               onclick="document.getElementById('fileInput').click()"
               ondragover="handleDragOver(event)"
               ondragleave="handleDragLeave(event)"
               ondrop="handleDrop(event)">
            <div class="dropzone-icon"><i class="bi bi-cloud-arrow-up"></i></div>
            <div class="dropzone-title">Arrastra tus archivos aquí</div>
            <div class="dropzone-sub">o haz clic para seleccionar</div>
            <span class="dropzone-limit">Hasta 2 GB gratis · Cifrado AES-256</span>
            <input type="file" id="fileInput" multiple hidden onchange="handleFileInput(event)" />
          </div>
          <div class="file-list" id="fileList"></div>
          <div class="progress-wrap" id="progressWrap">
            <div class="progress-label"><span id="progressText">Subiendo...</span><span id="progressPct">0%</span></div>
            <div class="progress-bar-track"><div class="progress-bar-fill" id="progressBar"></div></div>
          </div>
          <div class="upload-form" id="emailForm">
            <div class="row g-3 mt-1">
              <div class="col-12"><label class="form-label-custom">Para</label><input type="email" class="form-control-custom" placeholder="email@destinatario.com" /></div>
              <div class="col-12"><label class="form-label-custom">Tu email</label><input type="email" class="form-control-custom" placeholder="tuemail@ejemplo.com" /></div>
              <div class="col-12"><label class="form-label-custom">Mensaje (opcional)</label><textarea class="form-control-custom" rows="2" placeholder="Añade un mensaje..."></textarea></div>
            </div>
          </div>
          <div class="upload-form" id="linkForm" style="display:none">
            <div class="row g-3 mt-1">
              <div class="col-12"><label class="form-label-custom">Caducidad del enlace</label>
                <select class="form-select-custom"><option>7 días (por defecto)</option><option>1 día</option><option>3 días</option><option>30 días (Pro)</option></select></div>
              <div class="col-12"><label class="form-label-custom">Contraseña (opcional)</label><input type="password" class="form-control-custom" placeholder="Protege con contraseña" /></div>
            </div>
          </div>
          <div class="upload-form" id="requestForm" style="display:none">
            <div class="row g-3 mt-1">
              <div class="col-12"><label class="form-label-custom">Email del remitente</label><input type="email" class="form-control-custom" placeholder="solicitar a..." /></div>
              <div class="col-12"><label class="form-label-custom">Descripción</label><input type="text" class="form-control-custom" placeholder="¿Qué necesitas?" /></div>
            </div>
          </div>
          <button class="btn-primary-main w-100 mt-3" onclick="simulateUpload()" style="padding:.75rem;"><i class="bi bi-send me-2"></i>Transferir ahora</button>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- ==================== HOW IT WORKS ==================== -->
<section class="section" id="how-it-works" style="background:rgba(255,255,255,.02);border-top:1px solid var(--clr-border);border-bottom:1px solid var(--clr-border);">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Proceso</div>
      <h2 class="section-title">Tan fácil como 1, 2, 3</h2>
    </div>
    <div class="steps-grid">
      <div class="step-card"><div class="step-icon"><i class="bi bi-cloud-upload"></i></div><div class="step-number">01</div><div class="step-title">Selecciona tus archivos</div><div class="step-desc">Arrastra y suelta cualquier tipo de archivo, hasta 20 GB en plan Pro.</div></div>
      <div class="step-card"><div class="step-icon"><i class="bi bi-person-lines-fill"></i></div><div class="step-number">02</div><div class="step-title">Añade destinatarios</div><div class="step-desc">Introduce emails o genera un enlace con fecha de expiración personalizada.</div></div>
      <div class="step-card"><div class="step-icon"><i class="bi bi-send-check-fill"></i></div><div class="step-number">03</div><div class="step-title">Envía y monitorea</div><div class="step-desc">Pulsa "Transferir" y recibe notificaciones cuando se descarguen tus archivos.</div></div>
      <div class="step-card"><div class="step-icon"><i class="bi bi-download"></i></div><div class="step-number">04</div><div class="step-title">El destinatario descarga</div><div class="step-desc">Sin registro necesario. Un clic en el enlace y la descarga comienza al instante.</div></div>
    </div>
  </div>
</section>

<!-- ==================== PRICING ==================== -->
<section class="section" id="pricing">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Planes</div>
      <h2 class="section-title">Elige el plan perfecto</h2>
      <p class="section-subtitle mx-auto">Sin contratos. Sin sorpresas. Cancela cuando quieras.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-6 col-lg-4">
        <div class="plan-card">
          <div class="plan-name">Gratis</div>
          <div class="plan-price"><sup>$</sup>0<small>/mes</small></div>
          <div class="plan-desc">Ideal para uso personal ocasional</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> 2 GB por transferencia</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Hasta 5 destinatarios</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Enlace válido 7 días</div>
          <div class="plan-feature"><i class="bi bi-x-circle"></i> Sin contraseña en enlaces</div>
          <div class="plan-feature"><i class="bi bi-x-circle"></i> Sin historial</div>
          <button class="btn btn-plan btn-plan-outline" data-bs-toggle="modal" data-bs-target="#registerModal">Empezar gratis</button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="plan-card featured">
          <div class="plan-badge">⭐ Más popular</div>
          <div class="plan-name">Pro</div>
          <div class="plan-price"><sup>$</sup>12<small>/mes</small></div>
          <div class="plan-desc">Para freelancers y profesionales creativos</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> 20 GB por transferencia</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Destinatarios ilimitados</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Enlace válido 30 días</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Contraseña en enlaces</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Historial completo</div>
          <button class="btn btn-plan btn-plan-filled" data-bs-toggle="modal" data-bs-target="#registerModal">Empezar Pro</button>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="plan-card">
          <div class="plan-name">Business</div>
          <div class="plan-price"><sup>$</sup>29<small>/mes</small></div>
          <div class="plan-desc">Para equipos y empresas que escalan</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Sin límite de tamaño</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Destinatarios ilimitados</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Enlace sin caducidad</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Contraseña + 2FA</div>
          <div class="plan-feature"><i class="bi bi-check-circle-fill"></i> Analíticas avanzadas</div>
          <button class="btn btn-plan btn-plan-outline" data-bs-toggle="modal" data-bs-target="#registerModal">Contactar ventas</button>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ==================== FAQ ==================== -->
<section class="section" id="faq" style="background:rgba(255,255,255,.02);border-top:1px solid var(--clr-border);border-bottom:1px solid var(--clr-border);">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-4">
        <div class="section-label">FAQ</div>
        <h2 class="section-title">Preguntas frecuentes</h2>
        <p class="section-subtitle">¿Más dudas? <a href="#contact">Contáctanos</a></p>
      </div>
      <div class="col-lg-8">
        <div class="accordion" id="faqAccordion">
          <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">¿Necesito registrarme para enviar archivos?</button></h2><div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body">No. Puedes enviar archivos de hasta 2 GB sin crear una cuenta. Para funciones avanzadas como historial, contraseñas y archivos más grandes, te recomendamos el plan Pro.</div></div></div>
          <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">¿Cómo se protegen mis archivos?</button></h2><div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Todos los archivos se cifran con AES-256 en tránsito y en reposo. Usamos TLS 1.3 para todas las conexiones. Nuestros servidores están certificados ISO 27001.</div></div></div>
          <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">¿Por cuánto tiempo se almacenan los archivos?</button></h2><div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">En el plan gratuito, los archivos se eliminan a los 7 días. En Pro hasta 30 días. En Business puedes configurar la retención o almacenamiento indefinido.</div></div></div>
          <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">¿Funciona en dispositivos móviles?</button></h2><div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Sí. SwiftShare está diseñado bajo principios de Responsive Design con Bootstrap 5. Funciona perfectamente en iOS, Android y cualquier navegador moderno en tablets y smartphones.</div></div></div>
          <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">¿Puedo cancelar mi suscripción en cualquier momento?</button></h2><div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Por supuesto. No hay contratos ni penalizaciones. Puedes cancelar desde tu panel de control y seguirás teniendo acceso hasta el final del período pagado.</div></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== CONTACT FORM ==================== -->
<section class="section" id="contact">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-5">
        <div class="section-label">Contacto</div>
        <h2 class="section-title">Hablemos</h2>
        <p class="section-subtitle">¿Tienes preguntas, necesitas una demo o quieres conocer el plan Enterprise?</p>
        <div class="mt-4 d-flex flex-column gap-3">
          <div class="d-flex align-items-center gap-3">
            <div class="feature-icon icon-purple" style="margin-bottom:0;flex-shrink:0;"><i class="bi bi-envelope-fill"></i></div>
            <div><div style="font-size:.78rem;color:var(--clr-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Email</div><div style="font-size:.9rem;color:var(--clr-text);">hola@swiftshare.io</div></div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="feature-icon icon-green" style="margin-bottom:0;flex-shrink:0;"><i class="bi bi-headset"></i></div>
            <div><div style="font-size:.78rem;color:var(--clr-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Soporte</div><div style="font-size:.9rem;color:var(--clr-text);">Disponible 24/7 en chat</div></div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="feature-icon icon-pink" style="margin-bottom:0;flex-shrink:0;"><i class="bi bi-geo-alt-fill"></i></div>
            <div><div style="font-size:.78rem;color:var(--clr-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Oficina</div><div style="font-size:.9rem;color:var(--clr-text);">Ciudad de México · Amsterdam</div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="contact-card">
          <form id="contactForm" novalidate>
            <div class="row g-3">
              <div class="col-sm-6">
                <label class="form-label-custom">Nombre *</label>
                <div class="form-floating-custom"><input type="text" class="form-control-custom has-icon" id="cName" required placeholder="Tu nombre" /><i class="bi bi-person input-icon"></i></div>
              </div>
              <div class="col-sm-6">
                <label class="form-label-custom">Apellido *</label>
                <div class="form-floating-custom"><input type="text" class="form-control-custom has-icon" id="cLastName" required placeholder="Tu apellido" /><i class="bi bi-person input-icon"></i></div>
              </div>
              <div class="col-sm-6">
                <label class="form-label-custom">Email *</label>
                <div class="form-floating-custom"><input type="email" class="form-control-custom has-icon" id="cEmail" required placeholder="tuemail@ejemplo.com" /><i class="bi bi-envelope input-icon"></i></div>
              </div>
              <div class="col-sm-6">
                <label class="form-label-custom">Teléfono</label>
                <div class="form-floating-custom"><input type="tel" class="form-control-custom has-icon" placeholder="+52 55 0000 0000" /><i class="bi bi-telephone input-icon"></i></div>
              </div>
              <div class="col-12"><label class="form-label-custom">Empresa</label><input type="text" class="form-control-custom" placeholder="Nombre de tu empresa (opcional)" /></div>
              <div class="col-12">
                <label class="form-label-custom">Asunto *</label>
                <select class="form-select-custom" id="cSubject" required>
                  <option value="" selected disabled>Selecciona un tema...</option>
                  <option>Consulta sobre planes</option>
                  <option>Soporte técnico</option>
                  <option>Demo Enterprise</option>
                  <option>Partnerships</option>
                  <option>Otro</option>
                </select>
              </div>
              <div class="col-12"><label class="form-label-custom">Mensaje *</label><textarea class="form-control-custom" id="cMessage" rows="4" required placeholder="Cuéntanos cómo podemos ayudarte..."></textarea></div>
              <div class="col-12">
                <div class="d-flex align-items-start gap-2">
                  <input class="form-check-input mt-1" type="checkbox" id="cPrivacy" required />
                  <label class="form-check-label" for="cPrivacy">Acepto la <a href="#">Política de Privacidad</a> y el tratamiento de mis datos personales. *</label>
                </div>
              </div>
              <div class="col-12"><button type="submit" class="btn-primary-main w-100" style="padding:.8rem;"><i class="bi bi-send me-2"></i>Enviar mensaje</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== CTA ==================== -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="cta-banner">
      <div class="position-relative" style="z-index:1;">
        <h2 class="cta-title">Empieza a transferir hoy.<br>Es gratis.</h2>
        <p class="cta-sub">Únete a 12 millones de personas que confían en SwiftShare</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="#upload-zone" class="btn-primary-main" style="padding:.85rem 2.5rem;"><i class="bi bi-cloud-upload me-2"></i>Enviar archivos gratis</a>
          <a href="#pricing" class="btn-outline-main" style="padding:.85rem 2.5rem;">Ver planes Pro</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer>
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="footer-brand">Vasanta Transfer</div>
        <p class="footer-desc">La plataforma más rápida y segura para transferir archivos grandes desde cualquier dispositivo.</p>
        <div class="footer-socials">
          <a href="#" class="social-btn" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="social-btn" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="social-btn" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-btn" aria-label="GitHub"><i class="bi bi-github"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Producto</div>
        <ul class="footer-links">
          <li><a href="#features">Características</a></li>
          <li><a href="#pricing">Precios</a></li>
          <li><a href="#how-it-works">Cómo funciona</a></li>
          <li><a href="#">API Docs</a></li>
          <li><a href="#">Changelog</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Empresa</div>
        <ul class="footer-links">
          <li><a href="#">Nosotros</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="#">Prensa</a></li>
          <li><a href="#">Carreras</a></li>
          <li><a href="#contact">Contacto</a></li>
        </ul>
      </div>
      <div class="col-lg-4">
        <div class="footer-heading">Novedades</div>
        <p style="font-size:.85rem;color:var(--clr-muted);margin-bottom:1rem;">Suscríbete para recibir tips y novedades.</p>
        <div class="newsletter-input-wrap">
          <input type="email" class="form-control-custom" placeholder="tuemail@ejemplo.com" />
          <button class="btn-newsletter" onclick="subscribeNewsletter()">Suscribir</button>
        </div>
        <p style="font-size:.75rem;color:var(--clr-muted);margin-top:.6rem;">Sin spam. Cancela cuando quieras.</p>
      </div>
    </div>
    <hr class="footer-divider" />
    <div class="footer-bottom">
      <div class="footer-copy">© 2026Big Data Trasnfer. Todos los derechos reservados.</div>
      <div class="footer-legal"><a href="#">Privacidad</a><a href="#">Términos</a><a href="#">Cookies</a><a href="#">RGPD</a></div>
    </div>
  </div>
</footer>

<!-- ==================== MODAL LOGIN ==================== -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Iniciar sesión</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form novalidate>
          <div class="mb-3"><label class="form-label-custom">Email</label><div class="form-floating-custom"><input type="email" class="form-control-custom has-icon" required placeholder="tu@email.com" /><i class="bi bi-envelope input-icon"></i></div></div>
          <div class="mb-3"><label class="form-label-custom">Contraseña</label><div class="form-floating-custom"><input type="password" class="form-control-custom has-icon" required placeholder="••••••••" /><i class="bi bi-lock input-icon"></i></div></div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2"><input class="form-check-input" type="checkbox" id="rememberMe" /><label class="form-check-label" for="rememberMe">Recuérdame</label></div>
            <a href="#" style="font-size:.85rem;">¿Olvidaste tu contraseña?</a>
          </div>
          <button type="submit" class="btn-primary-main w-100" onclick="event.preventDefault();bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();showToast('bi-check-circle-fill','¡Bienvenido de vuelta!','var(--clr-accent3)');">Iniciar sesión</button>
          <div class="divider-line"></div>
          <div style="text-align:center;font-size:.85rem;color:var(--clr-muted);">¿No tienes cuenta? <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Regístrate gratis</a></div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ==================== MODAL REGISTER ==================== -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Crear cuenta gratis</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="registerForm" novalidate>
          <div class="row g-3">
            <div class="col-6"><label class="form-label-custom">Nombre *</label><input type="text" class="form-control-custom" required placeholder="Juan" /></div>
            <div class="col-6"><label class="form-label-custom">Apellido *</label><input type="text" class="form-control-custom" required placeholder="García" /></div>
            <div class="col-12"><label class="form-label-custom">Email *</label><div class="form-floating-custom"><input type="email" class="form-control-custom has-icon" required placeholder="tu@email.com" /><i class="bi bi-envelope input-icon"></i></div></div>
            <div class="col-12"><label class="form-label-custom">Contraseña *</label><div class="form-floating-custom"><input type="password" class="form-control-custom has-icon" required placeholder="Mín. 8 caracteres" /><i class="bi bi-lock input-icon"></i></div></div>
            <div class="col-12"><label class="form-label-custom">Confirmar contraseña *</label><input type="password" class="form-control-custom" required placeholder="Repite tu contraseña" /></div>
            <div class="col-12"><select class="form-select-custom"><option value="" disabled selected>¿Cómo planeas usar SwiftShare?</option><option>Personal</option><option>Freelancer</option><option>Pequeña empresa</option><option>Corporativo</option></select></div>
            <div class="col-12"><div class="d-flex align-items-start gap-2"><input class="form-check-input mt-1" type="checkbox" id="regTerms" required /><label class="form-check-label" for="regTerms">Acepto los <a href="#">Términos</a> y la <a href="#">Política de privacidad</a></label></div></div>
            <div class="col-12"><button type="submit" class="btn-primary-main w-100">Crear cuenta gratis</button></div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showToast(icon,msg,color='var(--clr-accent3)'){
  const wrap=document.getElementById('toastContainer');
  const t=document.createElement('div');
  t.className='custom-toast';
  t.style.borderLeft=`3px solid ${color}`;
  t.innerHTML=`<i class="bi ${icon} toast-icon" style="color:${color}"></i><span>${msg}</span>`;
  wrap.appendChild(t);
  t.style.animation='fadeInUp .3s ease';
  setTimeout(()=>{t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(()=>t.remove(),300);},3500);
}
function switchTab(btn,type){
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  ['emailForm','linkForm','requestForm'].forEach(id=>document.getElementById(id).style.display='none');
  const map={email:'emailForm',link:'linkForm',request:'requestForm'};
  document.getElementById(map[type]).style.display='block';
}
function handleDragOver(e){e.preventDefault();document.getElementById('dropzone').classList.add('drag-over');}
function handleDragLeave(){document.getElementById('dropzone').classList.remove('drag-over');}
function handleDrop(e){e.preventDefault();document.getElementById('dropzone').classList.remove('drag-over');renderFiles([...e.dataTransfer.files]);}
function handleFileInput(e){renderFiles([...e.target.files]);}
const fileExt={pdf:['bi-file-pdf','#ff5050'],doc:['bi-file-word','#2c7be5'],docx:['bi-file-word','#2c7be5'],xls:['bi-file-excel','#43e97b'],xlsx:['bi-file-excel','#43e97b'],zip:['bi-file-zip','#ffb432'],rar:['bi-file-zip','#ffb432'],mp4:['bi-camera-video','#a78bfa'],mov:['bi-camera-video','#a78bfa'],jpg:['bi-file-image','#ff6584'],jpeg:['bi-file-image','#ff6584'],png:['bi-file-image','#ff6584']};
function getFileIcon(name){const ext=name.split('.').pop().toLowerCase();return fileExt[ext]||['bi-file-earmark','var(--clr-muted)'];}
function formatSize(b){if(b<1024)return b+' B';if(b<1048576)return(b/1024).toFixed(1)+' KB';return(b/1048576).toFixed(1)+' MB';}
let selectedFiles=[];
function renderFiles(files){
  selectedFiles=[...selectedFiles,...files];
  const list=document.getElementById('fileList');
  list.style.display='block';list.innerHTML='';
  selectedFiles.forEach((f,i)=>{
    const[icon,color]=getFileIcon(f.name);
    const item=document.createElement('div');
    item.className='file-item';
    item.innerHTML=`<i class="bi ${icon} file-icon" style="color:${color}"></i><span class="file-name">${f.name}</span><span class="file-size">${formatSize(f.size)}</span><i class="bi bi-x file-remove" onclick="removeFile(${i})"></i>`;
    list.appendChild(item);
  });
  if(files.length>0) showToast('bi-check-circle-fill',`${files.length} archivo(s) añadido(s)`);
}
function removeFile(i){selectedFiles.splice(i,1);if(selectedFiles.length===0)document.getElementById('fileList').style.display='none';else renderFiles([]);}
function simulateUpload(){
  if(selectedFiles.length===0){showToast('bi-exclamation-triangle-fill','Selecciona al menos un archivo','var(--clr-accent2)');return;}
  const pw=document.getElementById('progressWrap'),pb=document.getElementById('progressBar'),pt=document.getElementById('progressText'),pp=document.getElementById('progressPct');
  pw.style.display='block';let pct=0;
  const iv=setInterval(()=>{
    pct+=Math.random()*15;
    if(pct>=100){pct=100;clearInterval(iv);pt.textContent='¡Transferencia completada!';showToast('bi-check2-circle','Archivos enviados correctamente 🎉','var(--clr-accent3)');setTimeout(()=>{pw.style.display='none';pb.style.width='0';},4000);}
    pb.style.width=pct+'%';pp.textContent=Math.round(pct)+'%';
  },200);
}
document.getElementById('contactForm').addEventListener('submit',function(e){
  e.preventDefault();this.classList.add('was-validated');
  if(this.checkValidity()){showToast('bi-envelope-check-fill','Mensaje enviado. Te respondemos pronto.','var(--clr-accent3)');this.reset();this.classList.remove('was-validated');}
  else showToast('bi-exclamation-circle-fill','Por favor completa todos los campos requeridos.','var(--clr-accent2)');
});
document.getElementById('registerForm').addEventListener('submit',function(e){
  e.preventDefault();this.classList.add('was-validated');
  if(this.checkValidity()){bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();showToast('bi-person-check-fill','¡Cuenta creada! Revisa tu email.','var(--clr-accent3)');}
});
function subscribeNewsletter(){showToast('bi-megaphone-fill','¡Gracias por suscribirte!','var(--clr-accent)');}
const sections=document.querySelectorAll('section[id]'),navLinks=document.querySelectorAll('.nav-link[href^="#"]');
window.addEventListener('scroll',()=>{
  let cur='';sections.forEach(s=>{if(window.scrollY>=s.offsetTop-120)cur=s.id;});
  navLinks.forEach(l=>{l.classList.remove('active');if(l.getAttribute('href')==='#'+cur)l.classList.add('active');});
});
</script>
</body>
</html>