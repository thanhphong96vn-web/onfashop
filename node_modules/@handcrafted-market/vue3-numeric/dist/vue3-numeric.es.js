import { toRefs, ref, computed, watch, nextTick, onMounted, unref, withDirectives, openBlock, createElementBlock, vModelText, toDisplayString } from "vue";
function __commonjs(fn, module) {
  return module = { exports: {} }, fn(module, module.exports), module.exports;
}
var settings = {
  symbol: "$",
  format: "%s%v",
  decimal: ".",
  thousand: ",",
  precision: 2,
  grouping: 3,
  stripZeros: false,
  fallback: 0
};
function unformat(value) {
  var decimal = arguments.length <= 1 || arguments[1] === void 0 ? settings.decimal : arguments[1];
  var fallback = arguments.length <= 2 || arguments[2] === void 0 ? settings.fallback : arguments[2];
  if (Array.isArray(value)) {
    return value.map(function(val) {
      return unformat(val, decimal, fallback);
    });
  }
  if (typeof value === "number")
    return value;
  var regex = new RegExp("[^0-9-(-)-" + decimal + "]", ["g"]);
  var unformattedValueString = ("" + value).replace(regex, "").replace(decimal, ".").replace(/\(([-]*\d*[^)]?\d+)\)/g, "-$1").replace(/\((.*)\)/, "");
  var negative = (unformattedValueString.match(/-/g) || 2).length % 2, absUnformatted = parseFloat(unformattedValueString.replace(/-/g, "")), unformatted = absUnformatted * (negative ? -1 : 1);
  return !isNaN(unformatted) ? unformatted : fallback;
}
function _checkPrecision(val, base) {
  val = Math.round(Math.abs(val));
  return isNaN(val) ? base : val;
}
function toFixed(value, precision) {
  precision = _checkPrecision(precision, settings.precision);
  var power = Math.pow(10, precision);
  return (Math.round((value + 1e-8) * power) / power).toFixed(precision);
}
var index = __commonjs(function(module) {
  var hasOwnProperty = Object.prototype.hasOwnProperty;
  var propIsEnumerable = Object.prototype.propertyIsEnumerable;
  function toObject(val) {
    if (val === null || val === void 0) {
      throw new TypeError("Object.assign cannot be called with null or undefined");
    }
    return Object(val);
  }
  module.exports = Object.assign || function(target, source) {
    var from;
    var to = toObject(target);
    var symbols;
    for (var s = 1; s < arguments.length; s++) {
      from = Object(arguments[s]);
      for (var key in from) {
        if (hasOwnProperty.call(from, key)) {
          to[key] = from[key];
        }
      }
      if (Object.getOwnPropertySymbols) {
        symbols = Object.getOwnPropertySymbols(from);
        for (var i = 0; i < symbols.length; i++) {
          if (propIsEnumerable.call(from, symbols[i])) {
            to[symbols[i]] = from[symbols[i]];
          }
        }
      }
    }
    return to;
  };
});
var objectAssign = index && typeof index === "object" && "default" in index ? index["default"] : index;
function _stripInsignificantZeros(str, decimal) {
  var parts = str.split(decimal);
  var integerPart = parts[0];
  var decimalPart = parts[1].replace(/0+$/, "");
  if (decimalPart.length > 0) {
    return integerPart + decimal + decimalPart;
  }
  return integerPart;
}
function formatNumber(number) {
  var opts = arguments.length <= 1 || arguments[1] === void 0 ? {} : arguments[1];
  if (Array.isArray(number)) {
    return number.map(function(val) {
      return formatNumber(val, opts);
    });
  }
  opts = objectAssign({}, settings, opts);
  var negative = number < 0 ? "-" : "";
  var base = parseInt(toFixed(Math.abs(number), opts.precision), 10) + "";
  var mod = base.length > 3 ? base.length % 3 : 0;
  var formatted = negative + (mod ? base.substr(0, mod) + opts.thousand : "") + base.substr(mod).replace(/(\d{3})(?=\d)/g, "$1" + opts.thousand) + (opts.precision > 0 ? opts.decimal + toFixed(Math.abs(number), opts.precision).split(".")[1] : "");
  return opts.stripZeros ? _stripInsignificantZeros(formatted, opts.decimal) : formatted;
}
var index$1 = __commonjs(function(module) {
  var strValue = String.prototype.valueOf;
  var tryStringObject = function tryStringObject2(value) {
    try {
      strValue.call(value);
      return true;
    } catch (e) {
      return false;
    }
  };
  var toStr = Object.prototype.toString;
  var strClass = "[object String]";
  var hasToStringTag = typeof Symbol === "function" && typeof Symbol.toStringTag === "symbol";
  module.exports = function isString2(value) {
    if (typeof value === "string") {
      return true;
    }
    if (typeof value !== "object") {
      return false;
    }
    return hasToStringTag ? tryStringObject(value) : toStr.call(value) === strClass;
  };
});
var isString = index$1 && typeof index$1 === "object" && "default" in index$1 ? index$1["default"] : index$1;
function _checkCurrencyFormat(format) {
  if (isString(format) && format.match("%v")) {
    return {
      pos: format,
      neg: format.replace("-", "").replace("%v", "-%v"),
      zero: format
    };
  }
  return format;
}
function formatMoney(number) {
  var opts = arguments.length <= 1 || arguments[1] === void 0 ? {} : arguments[1];
  if (Array.isArray(number)) {
    return number.map(function(val) {
      return formatMoney(val, opts);
    });
  }
  opts = objectAssign({}, settings, opts);
  var formats = _checkCurrencyFormat(opts.format);
  var useFormat = void 0;
  if (number > 0) {
    useFormat = formats.pos;
  } else if (number < 0) {
    useFormat = formats.neg;
  } else {
    useFormat = formats.zero;
  }
  return useFormat.replace("%s", opts.symbol).replace("%v", formatNumber(Math.abs(number), opts));
}
const _hoisted_1 = ["placeholder", "disabled"];
const _sfc_main = {
  props: {
    currency: {
      type: String,
      default: "",
      required: false
    },
    max: {
      type: Number,
      default: Number.MAX_SAFE_INTEGER || 9007199254740991,
      required: false
    },
    min: {
      type: Number,
      default: Number.MIN_SAFE_INTEGER || -9007199254740991,
      required: false
    },
    minus: {
      type: Boolean,
      default: false,
      required: false
    },
    placeholder: {
      type: String,
      default: "",
      required: false
    },
    emptyValue: {
      type: [Number, String],
      default: "",
      required: false
    },
    precision: {
      type: Number,
      default: 0,
      required: false
    },
    separator: {
      type: String,
      default: ",",
      required: false
    },
    thousandSeparator: {
      default: void 0,
      required: false,
      type: String
    },
    decimalSeparator: {
      default: void 0,
      required: false,
      type: String
    },
    outputType: {
      required: false,
      type: String,
      default: "Number"
    },
    modelValue: {
      type: [Number, String],
      default: 0,
      required: true
    },
    readOnly: {
      type: Boolean,
      default: false,
      required: false
    },
    readOnlyClass: {
      type: String,
      default: "",
      required: false
    },
    disabled: {
      type: Boolean,
      default: false,
      required: false
    },
    currencySymbolPosition: {
      type: String,
      default: "prefix",
      required: false
    },
    currencySymbolSpacing: {
      type: Boolean,
      default: true,
      required: false
    }
  },
  emits: ["change", "blur", "focus", "update:modelValue"],
  setup(__props, { emit }) {
    const props = __props;
    const { readOnly, separator, currency, precision } = toRefs(props);
    const amount = ref("");
    const numeric = ref(null);
    const spanReadOnly = ref(null);
    const amountNumber = computed(() => unformat$1(amount.value));
    const valueNumber = computed(() => unformat$1(props.modelValue));
    const decimalSeparatorSymbol = computed(() => {
      if (typeof props.decimalSeparator !== "undefined") {
        return props.decimalSeparator;
      }
      if (separator.value === ",") {
        return ".";
      }
      return ",";
    });
    const thousandSeparatorSymbol = computed(() => {
      if (typeof props.thousandSeparator !== "undefined") {
        return props.thousandSeparator;
      }
      if (separator.value === ".") {
        return ".";
      }
      if (separator.value === "space") {
        return " ";
      }
      return ",";
    });
    const symbolPosition = computed(() => {
      let suffix = "%v %s";
      let prefix = "%s %v";
      if (!currency.value) {
        return "%v";
      }
      if (props.currencySymbolSpacing === false) {
        suffix = "%v%s";
        prefix = "%s%v";
      }
      return props.currencySymbolPosition === "suffix" ? suffix : prefix;
    });
    watch(valueNumber, (newValue, oldValue) => {
      if (numeric.value !== document.activeElement) {
        amount.value = format(newValue);
      }
    });
    watch(readOnly, async (newValue, oldValue) => {
      if (oldValue === false && newValue === true) {
        await nextTick();
        spanReadOnly.className = props.readOnlyClass;
      }
    });
    watch(separator, (newValue, oldValue) => {
      process(valueNumber.value);
      amount.value = format(valueNumber.value);
    });
    watch(currency, (newValue, oldValue) => {
      process(valueNumber.value);
      amount.value = format(valueNumber.value);
    });
    watch(precision, (newValue, oldValue) => {
      process(valueNumber.value);
      amount.value = format(valueNumber.value);
    });
    onMounted(() => {
      if (valueNumber.value || isDeliberatelyZero()) {
        process(valueNumber.value);
        amount.value = format(valueNumber.value);
        setTimeout(() => {
          process(valueNumber.value);
          amount.value = format(valueNumber.value);
        }, 500);
      }
      if (readOnly.value)
        spanReadOnly.className = props.readOnlyClass;
    });
    function onChangeHandler(e) {
      emit("change", e);
    }
    function onBlurHandler(e) {
      emit("blur", e);
      amount.value = format(valueNumber.value);
    }
    function onFocusHandler(e) {
      emit("focus", e);
      if (valueNumber.value === 0) {
        amount.value = null;
      } else {
        amount.value = formatMoney(valueNumber.value, {
          symbol: "",
          format: "%v",
          thousand: "",
          decimal: decimalSeparatorSymbol.value,
          precision: Number(precision.value)
        });
      }
    }
    function onInputHandler() {
      process(amountNumber.value);
    }
    function process(value) {
      if (value >= props.max) {
        update(props.max);
      }
      if (value <= props.min) {
        update(props.min);
      }
      if (value > props.min && value < props.max) {
        update(value);
      }
      if (!props.minus && value < 0) {
        props.min >= 0 ? update(props.min) : update(0);
      }
    }
    function update(value) {
      const fixedValue = toFixed(value, precision.value);
      const output = props.outputType.toLowerCase() === "string" ? fixedValue : Number(fixedValue);
      emit("update:modelValue", output);
    }
    function format(value) {
      return formatMoney(value, {
        symbol: currency.value,
        format: symbolPosition.value,
        precision: Number(precision.value),
        decimal: decimalSeparatorSymbol.value,
        thousand: thousandSeparatorSymbol.value
      });
    }
    function unformat$1(value) {
      const toUnformat = typeof value === "string" && value === "" ? props.emptyValue : value;
      return unformat(toUnformat, decimalSeparatorSymbol.value);
    }
    function isDeliberatelyZero() {
      return valueNumber.value === 0 && props.modelValue !== "";
    }
    return (_ctx, _cache) => {
      return !unref(readOnly) ? withDirectives((openBlock(), createElementBlock("input", {
        key: 0,
        ref: (_value, _refs) => {
          _refs["numeric"] = _value;
          numeric.value = _value;
        },
        placeholder: __props.placeholder,
        disabled: __props.disabled,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => amount.value = $event),
        type: "tel",
        onBlur: onBlurHandler,
        onInput: onInputHandler,
        onFocus: onFocusHandler,
        onChange: onChangeHandler
      }, null, 40, _hoisted_1)), [
        [vModelText, amount.value]
      ]) : (openBlock(), createElementBlock("span", {
        key: 1,
        ref: (_value, _refs) => {
          _refs["spanReadOnly"] = _value;
          spanReadOnly.value = _value;
        }
      }, toDisplayString(amount.value), 513));
    };
  }
};
export { _sfc_main as default };
