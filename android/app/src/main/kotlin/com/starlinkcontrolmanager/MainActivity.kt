package com.starlinkcontrolmanager

import android.annotation.SuppressLint
import android.app.DownloadManager
import android.content.Context
import android.content.Intent
import android.net.Uri
import android.net.http.SslError
import android.os.Build
import android.os.Bundle
import android.os.Environment
import android.view.ViewGroup
import android.webkit.CookieManager
import android.webkit.DownloadListener
import android.webkit.SslErrorHandler
import android.webkit.URLUtil
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.BackHandler
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableFloatStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView

class MainActivity : ComponentActivity() {

    companion object {
        // URL Oficial del Sistema Starlink Control Manager
        const val APP_URL = "https://162.245.188.254/pctronic/public/"
    }

    private var webView: WebView? = null
    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null

    // Selector de Archivos (Fotos de Comprobantes de Pago y Documentos)
    private val fileChooserLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (fileUploadCallback == null) return@registerForActivityResult

        val results: Array<Uri>? = if (result.resultCode == RESULT_OK) {
            val data = result.data
            if (data?.data != null) {
                arrayOf(data.data!!)
            } else if (data?.clipData != null) {
                val clipData = data.clipData!!
                Array(clipData.itemCount) { i -> clipData.getItemAt(i).uri }
            } else {
                null
            }
        } else {
            null
        }

        fileUploadCallback?.onReceiveValue(results)
        fileUploadCallback = null
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()

        setContent {
            StarlinkApp()
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    @Composable
    fun StarlinkApp() {
        var pageProgress by remember { mutableFloatStateOf(0f) }
        var isLoading by remember { mutableStateOf(true) }
        var canGoBack by remember { mutableStateOf(false) }

        // Manejo del botón físico / gesto Atrás de Android
        BackHandler(enabled = canGoBack) {
            if (webView?.canGoBack() == true) {
                webView?.goBack()
            }
        }

        Surface(
            modifier = Modifier.fillMaxSize(),
            color = Color(0xFF090E17)
        ) {
            Box(modifier = Modifier.fillMaxSize()) {
                // 1. WebView Principal
                AndroidView(
                    modifier = Modifier.fillMaxSize(),
                    factory = { context ->
                        WebView(context).apply {
                            webView = this
                            layoutParams = ViewGroup.LayoutParams(
                                ViewGroup.LayoutParams.MATCH_PARENT,
                                ViewGroup.LayoutParams.MATCH_PARENT
                            )

                            // Configuración avanzada de WebView para PWA de alto rendimiento
                            settings.apply {
                                javaScriptEnabled = true
                                domStorageEnabled = true
                                databaseEnabled = true
                                allowFileAccess = true
                                allowContentAccess = true
                                useWideViewPort = true
                                loadWithOverviewMode = true
                                displayZoomControls = false
                                builtInZoomControls = false
                                setSupportZoom(false)
                                cacheMode = WebSettings.LOAD_DEFAULT
                                mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW
                                userAgentString = "${userAgentString} StarlinkControlApp/1.0 (Android)"
                            }

                            // Habilitar cookies y almacenamiento de sesión
                            val cookieManager = CookieManager.getInstance()
                            cookieManager.setAcceptCookie(true)
                            cookieManager.setAcceptThirdPartyCookies(this, true)

                            // Manejador de Navegación y URLs externas (WhatsApp, Teléfono)
                            webViewClient = object : WebViewClient() {
                                override fun shouldOverrideUrlLoading(
                                    view: WebView?,
                                    request: WebResourceRequest?
                                ): Boolean {
                                    val url = request?.url?.toString() ?: return false

                                    // Si es WhatsApp -> abrir app nativa de WhatsApp
                                    if (url.startsWith("https://wa.me/") || url.startsWith("whatsapp://")) {
                                        try {
                                            val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                                            startActivity(intent)
                                            return true
                                        } catch (e: Exception) {
                                            Toast.makeText(context, "WhatsApp no está instalado", Toast.LENGTH_SHORT).show()
                                        }
                                    }

                                    // Si es llamada telefónica
                                    if (url.startsWith("tel:")) {
                                        try {
                                            val intent = Intent(Intent.ACTION_DIAL, Uri.parse(url))
                                            startActivity(intent)
                                            return true
                                        } catch (e: Exception) {
                                            // Ignorar si no tiene teléfono
                                        }
                                    }

                                    // Si es correo electrónico
                                    if (url.startsWith("mailto:")) {
                                        try {
                                            val intent = Intent(Intent.ACTION_SENDTO, Uri.parse(url))
                                            startActivity(intent)
                                            return true
                                        } catch (e: Exception) {
                                            // Ignorar
                                        }
                                    }

                                    return false
                                }

                                @SuppressLint("WebViewClientOnReceivedSslError")
                                override fun onReceivedSslError(
                                    view: WebView?,
                                    handler: SslErrorHandler?,
                                    error: SslError?
                                ) {
                                    // Permitir certificados en conexión por IP directa
                                    handler?.proceed()
                                }

                                override fun onPageFinished(view: WebView?, url: String?) {
                                    super.onPageFinished(view, url)
                                    isLoading = false
                                    canGoBack = view?.canGoBack() == true
                                }
                            }

                            // Manejador de Carga de Archivos y Barra de Progreso
                            webChromeClient = object : WebChromeClient() {
                                override fun onProgressChanged(view: WebView?, newProgress: Int) {
                                    pageProgress = newProgress / 100f
                                    if (newProgress == 100) {
                                        isLoading = false
                                    }
                                }

                                override fun onShowFileChooser(
                                    webView: WebView?,
                                    filePathCallback: ValueCallback<Array<Uri>>?,
                                    fileChooserParams: FileChooserParams?
                                ): Boolean {
                                    fileUploadCallback?.onReceiveValue(null)
                                    fileUploadCallback = filePathCallback

                                    val intent = fileChooserParams?.createIntent() ?: Intent(Intent.ACTION_GET_CONTENT).apply {
                                        type = "*/*"
                                        addCategory(Intent.CATEGORY_OPENABLE)
                                    }

                                    try {
                                        fileChooserLauncher.launch(intent)
                                    } catch (e: Exception) {
                                        fileUploadCallback = null
                                        Toast.makeText(context, "No se pudo abrir el selector de archivos", Toast.LENGTH_SHORT).show()
                                        return false
                                    }
                                    return true
                                }
                            }

                            // Manejador de Descargas Nativo (Recibos PDF, Estados de Cuenta, Backups SQL)
                            setDownloadListener(DownloadListener { url, userAgent, contentDisposition, mimetype, _ ->
                                try {
                                    val request = DownloadManager.Request(Uri.parse(url)).apply {
                                        setMimeType(mimetype)
                                        addRequestHeader("Cookie", CookieManager.getInstance().getCookie(url))
                                        addRequestHeader("User-Agent", userAgent)
                                        setDescription("Descargando archivo de Starlink Control...")
                                        setTitle(URLUtil.guessFileName(url, contentDisposition, mimetype))
                                        setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                                        setDestinationInExternalPublicDir(
                                            Environment.DIRECTORY_DOWNLOADS,
                                            URLUtil.guessFileName(url, contentDisposition, mimetype)
                                        )
                                    }

                                    val dm = context.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
                                    dm.enqueue(request)
                                    Toast.makeText(context, "Descarga iniciada... revisa tus notificaciones", Toast.LENGTH_LONG).show()
                                } catch (e: Exception) {
                                    // Si falla DownloadManager, intentar abrir en navegador
                                    try {
                                        val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                                        startActivity(intent)
                                    } catch (ex: Exception) {
                                        Toast.makeText(context, "Error al procesar la descarga", Toast.LENGTH_SHORT).show()
                                    }
                                }
                            })

                            loadUrl(APP_URL)
                        }
                    }
                )

                // 2. Barra de Progreso Superior
                if (pageProgress in 0.01f..0.99f) {
                    LinearProgressIndicator(
                        progress = { pageProgress },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(3.dp)
                            .align(Alignment.TopCenter),
                        color = Color(0xFF38BDF8),
                        trackColor = Color(0xFF0F172A)
                    )
                }

                // 3. Splash Screen Inicial de Carga
                AnimatedVisibility(
                    visible = isLoading,
                    enter = fadeIn(),
                    exit = fadeOut(),
                    modifier = Modifier.fillMaxSize()
                ) {
                    Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .background(Color(0xFF090E17)),
                        contentAlignment = Alignment.Center
                    ) {
                        Column(
                            horizontalAlignment = Alignment.CenterHorizontally
                        ) {
                            Image(
                                painter = painterResource(id = R.drawable.app_logo),
                                contentDescription = "Logo Starlink Control",
                                modifier = Modifier.size(110.dp)
                            )
                            Spacer(modifier = Modifier.height(20.dp))
                            Text(
                                text = "Starlink Control Manager",
                                color = Color.White,
                                fontSize = 18.sp,
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(6.dp))
                            Text(
                                text = "Conectando al servidor central...",
                                color = Color(0xFF94A3B8),
                                fontSize = 13.sp
                            )
                            Spacer(modifier = Modifier.height(24.dp))
                            CircularProgressIndicator(
                                modifier = Modifier.size(28.dp),
                                color = Color(0xFF38BDF8),
                                strokeWidth = 2.5.dp
                            )
                        }
                    }
                }
            }
        }
    }
}
