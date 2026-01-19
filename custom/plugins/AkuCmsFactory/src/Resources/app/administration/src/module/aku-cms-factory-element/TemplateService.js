const httpClient = Shopware.Application.getContainer('init').httpClient;

const headers = {
    Accept: 'application/vnd.api+json',
    Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
    'Content-Type': 'application/json'
};

let getCode = function (field, prefix, indent) {
    if (undefined == prefix) {
        prefix = 'data'
        indent = '';
    }
    if ('media' == field.type) {
        return getCodeMedia(field, prefix, indent);
    }
    if ('gallery' == field.type) {
        return getCodeGallery(field, prefix, indent);
    }
    if ('text-editor' == field.type) {
        return getCodeTextEditor(field, prefix, indent);
    }
    if ('textarea' == field.type) {
        return getCodeTextarea(field, prefix, indent);
    }
    if ('category' == field.type) {
        return getCodeCategory(field, prefix, indent);
    }
    if ('manufacturer' == field.type) {
        return getCodeManufacturer(field, prefix, indent);
    }
    if ('custom-entity' == field.type) {
        return getCodeCustomEntity(field, prefix, indent);
    }
    if ('product' == field.type) {
        return getCodeProduct(field, prefix, indent);
    }
    if ('color' == field.type) {
        return getCodeColor(field, prefix, indent);
    }
    if ('checkbox' == field.type) {
        return getCodeCheckbox(field, prefix, indent);
    }
    if ('stream' == field.type) {
        return getCodeStream(field, prefix, indent);
    }
    if ('repeater' == field.type) {
        return getCodeRepeater(field, prefix, indent);
    }
    return getCodeText(field, prefix, indent)
}

let getCodeCategory = function (field, prefix, indent) {
    return `` +
        `${indent}{# Category link #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    <p><a href="{{ seoUrl('frontend.navigation.page', { navigationId:${prefix}.${field.name}.getId()}) }}">\n` +
        `${indent}       {{ ${prefix}.${field.name}.translated.name }}\n` +
        `${indent}    </a></p>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeManufacturer = function (field, prefix, indent) {
    return `` +
        `${indent}{# Manufacturer name #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    <ul>\n` +
        `${indent}      {% for manufacturer in ${prefix}.${field.name} %} \n` +
        `${indent}            <li>{{ manufacturer.translated.name }}</li>\n` +
        `${indent}      {% endfor %}\n` +
        `${indent}    </ul>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeCustomEntity = function (field, prefix, indent) {
    return `` +
        `${indent}{# Custom Entity #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    <ul>\n` +
        `${indent}     {% for entity in ${prefix}.${field.name} %} \n` +
        `${indent}           <li>{{ entity.${field.entityLabelProperty}}}</li>\n` +
        `${indent}     {% endfor %}\n` +
        `${indent}    </ul>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeProduct = function (field, prefix, indent) {
    return `` +
        `${indent}{# Product link #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    <p><a href="{{ seoUrl('frontend.detail.page', { productId:${prefix}.${field.name}.getId()}) }}">\n` +
        `${indent}       {{ ${prefix}.${field.name}.translated.name }}\n` +
        `${indent}    </a></p>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeColor = function (field, prefix, indent) {
    return `` +
        `${indent}{# Color #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    <p style="color: {{ ${prefix}.${field.name} }};">Color: {{ ${prefix}.${field.name} }}</p>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeText = function (field, prefix, indent) {
    return `` +
        `${indent}{# Text #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined %}\n` +
        `${indent}    <p>{{ ${prefix}.${field.name} }}</p>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeCheckbox = function (field, prefix, indent) {
    return `` +
        `${indent}{# Checkbox #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined %}\n` +
        `${indent}    {% if true == ${prefix}.${field.name} %}\n` +
        `${indent}        <p>TRUE</p>\n` +
        `${indent}    {% else %}\n` +
        `${indent}        <p>FALSE</p>\n` +
        `${indent}    {% endif %}\n` +
        `${indent}{% endif %}\n`;
}

let getCodeTextarea = function (field, prefix, indent) {
    return `` +
        `${indent}{# Multiline Text #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined %}\n` +
        `${indent}    <div>{{ ${prefix}.${field.name}|nl2br }}</div>\n` +
        `${indent}{% endif %}\n`;
}

let getCodeTextEditor = function (field, prefix, indent) {
    return `` +
        `${indent}{# HTML #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined %}\n` +
        `${indent}    {{ ${prefix}.${field.name}|raw }}\n` +
        `${indent}{% endif %}\n`;
}

let getCodeChoice = function (field, prefix, indent) {
    return `` +
        `${indent}{# Choice #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined %}\n` +
        `${indent}    {{ ${prefix}.${field.name}|raw }}\n` +
        `${indent}{% endif %}\n`;
}

let getCodeMedia = function (field, prefix, indent) {
    return `` +
        `${indent}{# Media #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}    {% sw_thumbnails 'thumbnails' with {\n` +
        `${indent}        media: ${prefix}.${field.name}\n` +
        `${indent}    } %}\n` +
        `${indent}{% endif %}\n`;
}

let getCodeGallery = function (field, prefix, indent) {
    let that = this;
    let sub = 'med';
    let code = `` +
        `${indent}{# Gallery #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is iterable %}\n` +
        `${indent}    {% for ${sub} in ${prefix}.${field.name} %}\n`;

    code += `` +
        `${indent}        {% if ${sub} is defined and ${sub} is not null %}\n` +
        `${indent}            {% sw_thumbnails 'thumbnails' with {\n` +
        `${indent}                media: ${sub}\n` +
        `${indent}            } %}\n` +
        `${indent}        {% endif %}\n`;

    code += `` +
        `${indent}    {% endfor %}\n` +
        `${indent}{% endif %}\n`;
    return code;
}

let getCodeStream = function (field, prefix, indent) {
    return `` +
        `${indent}{# Stream - dynamic product group #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is not null %}\n` +
        `${indent}   <p>\n` +
        `${indent}       {{ ${prefix}.${field.name}.translated.name }}\n` +
        `${indent}   </p>\n` +
        `${indent}   {% if ${prefix}.${field.name}.extensions.products is defined and ${prefix}.${field.name}.extensions.products is iterable %}\n` +
        `${indent}      <ul>\n` +
        `${indent}      {% for stream_product in ${prefix}.${field.name}.extensions.products %}\n` +
        `${indent}          <li>{{stream_product.translated.name}}</li>\n` +
        `${indent}      {% endfor %}\n` +
        `${indent}      </ul>\n` +
        `${indent}   {% endif %}\n` +
        `${indent}{% endif %}\n`;
}

let getCodeRepeater = function (field, prefix, indent) {
    let that = this;
    let children = field.children;
    let level = Math.round(indent.length / 8).toString();
    let sub = 'sub' + level;
    let code = `` +
        `${indent}{# Repeater #}\n` +
        `${indent}{% if ${prefix}.${field.name} is defined and ${prefix}.${field.name} is iterable %}\n` +
        `${indent}    <div class="row">\n` +
        `${indent}    {% for ${sub} in ${prefix}.${field.name} %}\n` +
        `${indent}        <div class="col">\n`;

    children.forEach(function (child) {
        code += getCode(child, sub, `${indent}        `);
    })

    code += `` +
        `${indent}        </div>\n` +
        `${indent}    {% endfor %}\n` +
        `${indent}    </div>\n` +
        `${indent}{% endif %}\n`;
    return code;
}

export default {
    getSampleTemplate: function (fields) {
        let code = ''

        if (0 == fields.length) {
            return '';
        }
        fields.forEach(function (field) {
            code += getCode(field);
        })
        return code;
    },
    renderTemplate: async function (template_string, fields, values) {
        return await httpClient.post('/aku/cms-factory/render-template', {
            'template': template_string,
            'fields': Array.isArray(fields) ? fields : [],
            'values': values ? values : {}
        }, {headers});
    }
}