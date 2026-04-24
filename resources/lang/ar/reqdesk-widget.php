<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'أداة الدعم',
        'group' => 'ريكدسك',
    ],
    'page' => [
        'title' => 'أداة دعم ريكدسك',
        'subheading' => 'اضبط أداة ريكدسك المضمّنة لهذه اللوحة.',
        'saved' => 'تم حفظ الإعدادات.',
    ],
    'tabs' => [
        'connection' => 'الاتصال',
        'appearance' => 'المظهر',
        'layout' => 'التخطيط',
        'localization' => 'اللغة',
        'identity' => 'الهوية',
        'actions' => 'إجراءات مخصصة',
        'advanced' => 'متقدم',
    ],
    'fields' => [
        'api_key' => [
            'label' => 'مفتاح API',
            'help' => 'مفتاح مشروع (rqd_pk_) أو مفتاح مساحة عمل (rqd_ws_) من لوحة تحكم ريكدسك.',
        ],
        'api_url' => [
            'label' => 'رابط API',
            'help' => 'اتركه فارغًا لاستخدام https://app.reqdesk.com.',
        ],
        'signing_secret' => [
            'label' => 'سر التوقيع',
            'help' => 'سر HMAC لتوقيع هوية المستخدم المُصادق عليه. احتفظ به في الخادم فقط.',
        ],
        'theme_primary_color' => ['label' => 'اللون الأساسي'],
        'theme_mode' => ['label' => 'نمط المظهر'],
        'theme_border_radius' => ['label' => 'نصف قطر الحواف'],
        'theme_font_family' => ['label' => 'عائلة الخط'],
        'theme_z_index' => ['label' => 'ترتيب الطبقات'],
        'theme_logo' => ['label' => 'رابط الشعار'],
        'theme_brand_name' => ['label' => 'اسم العلامة'],
        'theme_hide_branding' => ['label' => 'إخفاء علامة ريكدسك'],
        'position' => ['label' => 'موضع الزر العائم'],
        'display_mode' => ['label' => 'نمط العرض'],
        'display_side' => ['label' => 'جهة القائمة الجانبية'],
        'display_width' => ['label' => 'عرض اللوحة'],
        'display_height' => ['label' => 'ارتفاع اللوحة'],
        'display_dismiss_on_backdrop' => ['label' => 'إغلاق عند النقر على الخلفية'],
        'hide_fab' => ['label' => 'إخفاء الزر العائم'],
        'hide_display_mode_picker' => ['label' => 'إخفاء مُحدّد نمط العرض'],
        'fab_icon' => ['label' => 'أيقونة الزر العائم'],
        'default_language' => ['label' => 'اللغة الافتراضية'],
        'widget_mode' => ['label' => 'وضع الأداة'],
        'default_category' => ['label' => 'معرّف التصنيف الافتراضي'],
        'translations' => ['label' => 'استبدالات الترجمة'],
        'auth_mode_when_signed' => [
            'label' => 'أوضاع المصادقة للمستخدم المُسجّل',
            'help' => 'تُطبّق عند وجود مستخدم Laravel مُصادق عليه.',
        ],
        'auth_mode_when_anonymous' => [
            'label' => 'أوضاع المصادقة للمجهول',
            'help' => 'تُطبّق في غياب مستخدم Laravel مُصادق عليه.',
        ],
        'user_resolver' => [
            'label' => 'مُحلِّل المستخدم',
            'help' => 'اسم فئة يُنفّذ WidgetUserResolver. الافتراضي DefaultUserResolver يقرأ $user->email و $user->name.',
        ],
        'actions' => ['label' => 'إجراءات القائمة المخصصة'],
        'enabled' => ['label' => 'تفعيل الأداة'],
        'inject_for_guests' => ['label' => 'إظهار للزوار غير المسجلين أيضًا'],
        'panels' => ['label' => 'قصر على لوحات محددة'],
        'script_url' => [
            'label' => 'رابط السكربت البديل',
            'help' => 'اتركه فارغًا لاستخدام الرابط الافتراضي المُثبَّت.',
        ],
    ],
    'actions' => [
        'save' => 'حفظ الإعدادات',
        'test_connection' => 'اختبار الاتصال',
        'preview_signature' => 'معاينة التوقيع',
    ],
    'validation' => [
        'invalid_resolver' => 'الفئة :class غير موجودة أو لا تُنفّذ WidgetUserResolver.',
        'invalid_color' => 'يجب أن يكون اللون الأساسي قيمة CSS سداسية صالحة.',
    ],
    'errors' => [
        'missing_api_key' => 'REQDESK_API_KEY غير مُكوّن. لن تظهر أداة ريكدسك.',
        'missing_signing_secret' => 'REQDESK_SIGNING_SECRET غير مُكوّن. هوية المستضيف الموقَّعة معطّلة.',
    ],
];
