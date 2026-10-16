"use strict";
$(document).ready(function () {

    select2();
    datatable();
    ckediter();
    setInterval(() => {
        feather.replace();
    }, 1000);
});

$(document).on("click", ".customModal", function () {

    var modalTitle = $(this).data("title");
    var modalUrl = $(this).data("url");
    var modalSize = $(this).data("size") == "" ? "md" : $(this).data("size");
    $("#customModal .modal-title").html(modalTitle);
    $("#customModal .modal-dialog").addClass("modal-" + modalSize);
    $.ajax({
        url: modalUrl,
        success: function (result) {
            if (result.status == "error") {
                notifier.show(
                    "Error!",
                    result.messages,
                    "error",
                    errorImg,
                    4000
                );
            } else {
                $("#customModal .body").html(result);
                $("#customModal").modal("show");
                select2();
                ckediter();
            }
        },
        error: function (result) {},
    });
});

function taskCheckbox() {
    const $checkboxes = $("#check-list input[type=checkbox]");

    const total = $checkboxes.length;
    const checked = $checkboxes.filter(":checked").length;

    const percentage = total ? Math.round((checked / total) * 100) : 0;

    const $progress = $("#taskProgress");

    $(".custom-label").text(percentage + "%");
    $progress.css("width", percentage + "%");
    $progress.removeClass("bg-danger bg-warning bg-primary bg-success");
    let progressClass = "bg-success";

    if (percentage <= 15) {
        progressClass = "bg-danger";
    } else if (percentage <= 33) {
        progressClass = "bg-warning";
    } else if (percentage <= 70) {
        progressClass = "bg-primary";
    }

    $progress.addClass(progressClass);
}

// basic message
$(document).on("click", ".confirm_dialog", function (e) {

    var title = $(this).attr("data-dialog-title");
    if (title == undefined) {
        var title = "Are you sure you want to delete this record ?";
    }
    var text = $(this).attr("data-dialog-text");
    if (text == undefined) {
        var text = "This record can not be restore after delete. Do you want to confirm?";
    }
    var dialogForm = $(this).closest("form");
    Swal.fire({
        title: title,
        text: text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
    }).then((data) => {
        if (data.isConfirmed) {
            dialogForm.submit();
        }
    });
});

// common
$(document).on("click", ".common_confirm_dialog", function (e) {

    var dialogForm = $(this).closest("form");
    var actions = $(this).data("actions");
    Swal.fire({
        title: "Are you sure you want to delete " + actions + " ?",
        text: "This " +
            actions +
            " can not be restore after delete. Do you want to confirm?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes",
    }).then((data) => {
        if (data.isConfirmed) {
            dialogForm.submit();
        }
    });
});

$(document).on("click", ".fc-day-grid-event", function (e) {

    e.preventDefault();
    var event = $(this);
    var modalTitle = $(this).find(".fc-content .fc-title").html();
    var modalSize = "md";
    var modalUrl = $(this).attr("href");
    $("#customModal .modal-title").html(modalTitle);
    $("#customModal .modal-dialog").addClass("modal-" + modalSize);
    $.ajax({
        url: modalUrl,
        success: function (result) {
            $("#customModal .modal-body").html(result);
            $("#customModal").modal("show");
        },
        error: function (result) {},
    });
});

function toastrs(title, message, status) {

    if (status == "success") {
        notifier.show("Success!", message, "success", successImg, 4000);
    } else {
        notifier.show("Error!", message, "error", errorImg, 4000);
    }
}

function convertArrayToJson(form) {

    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function (n, i) {
        indexed_array[n["name"]] = n["value"];
    });

    return indexed_array;
}

function select2(container = document) {
    $(container).find(".select2, .hidesearch").each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }
        let isHideSearch = $(this).hasClass('hidesearch');
        let $modal = $(this).closest('.modal');
        let config = {
            width: "100%",
            minimumResultsForSearch: isHideSearch ? Infinity : 0
        };
        if ($modal.length) {
            config.dropdownParent = $modal;
        }
        $(this).select2(config);
    });
}
 

function datatable() {


    if ($(".basic-datatable").length > 0) {
        $(".basic-datatable").DataTable({
            scrollX: true,
            dom: "Bfrtip",
            buttons: ["copy", "csv", "excel", "print"],
        });
    }

    if ($(".advance-datatable").length > 0) {
        $(".advance-datatable").DataTable({
            scrollX: true,
            ordering: false,
            stateSave: false,
            dom: "Bfrtip",
            buttons: [{
                    extend: "excelHtml5",
                    exportOptions: {
                        columns: ":visible",
                    },
                },
                {
                    extend: "pdfHtml5",
                    exportOptions: {
                        columns: ":visible",
                    },
                },
                {
                    extend: "copyHtml5",
                    exportOptions: {
                        columns: ":visible",
                    },
                },

                "colvis",
            ],
        });
    }
}

const R = {
    img: null,
    originalSrc: null,
    handles: [],
    tracking: false,
    resizing: false,
    corner: null,
    startX: 0,
    startW: 0,
    raf: null,
};

function ckediter(selector = ".classic-editor") {

    const elements = document.querySelectorAll(selector);

    if (elements.length === 0) return;

    elements.forEach((element) => {

        // Already initialized હોય તો ફરી CKEditor create ન કરો
        if (element.dataset.ckeditorInitialized === "true") {
            return;
        }

        element.dataset.ckeditorInitialized = "true";

        ClassicEditor.create(element, {
            ckfinder: {
                uploadUrl: $('meta[name="ckeditor-upload-url"]').attr("content"),
            },

            image: {
                styles: [
                    "alignLeft",
                    "alignCenter",
                    "alignRight"
                ],
                toolbar: [
                    "imageStyle:alignLeft",
                    "imageStyle:alignCenter",
                    "imageStyle:alignRight",
                    "|",
                    "imageTextAlternative",
                ],
            },

            mediaEmbed: {
                previewsInData: true
            },

        })
        .then((editor) => {

            // Multiple editors માટે દરેક editor અલગથી handle થશે
            registerTextAlignment(editor);
            initImageResize(editor);
            addAlignButtons(editor);

        })
        .catch((error) => {
            console.error("CKEditor error:", error);

            // જો initialization fail થાય તો ફરી initialize કરવાની permission
            delete element.dataset.ckeditorInitialized;
        });
    });
}

$(document).ready(function () {
    ckediter();
});

function registerTextAlignment(editor) {

    if (editor.commands.get("alignment")) return;
    editor.model.schema.extend("$block", {
        allowAttributes: "alignment"
    });
    editor.conversion.for("downcast").attributeToAttribute({
        model: "alignment",
        view: (v) =>
            !v || v === "left" ?
            null : {
                key: "style",
                value: `text-align:${v};`
            },
    });



    editor.conversion.for("upcast").attributeToAttribute({

        view: {
            key: "style",
            value: {
                "text-align": /left|right|center|justify/
            },
        },

        model: {
            key: "alignment",
            value: (el) => el.getStyle("text-align")
        },

    });

}



function alignText(editor, value) {

    if (editor.commands.get("alignment")) {

        editor.execute("alignment", {
            value
        });

    } else {

        editor.model.change((writer) => {

            Array.from(

                editor.model.document.selection.getSelectedBlocks(),

            ).forEach((b) => writer.setAttribute("alignment", value, b));

        });

    }

    editor.editing.view.focus();

}

function initImageResize(editor) {

    const editable = editor.ui.getEditableElement();
    editable.addEventListener("click", (e) => {

        const img = e.target.closest("img");
        document
            .querySelectorAll(".ck-content img")

            .forEach((i) => (i.style.outline = ""));
        if (!img) {
            deselectImage();
            return;
        }
        R.img = img;
        R.originalSrc = img.src;
        img.style.outline = "2px solid #0d6efd";
        buildHandles();
        startTracking();

    });



    new MutationObserver(() => {
        if (R.img && !R.img.isConnected) deselectImage();

    }).observe(editable, {
        childList: true,
        subtree: true,
        characterData: true,

    });



    document.addEventListener("mousemove", (e) => {
        if (!R.resizing) return;
        if (R.raf) cancelAnimationFrame(R.raf);
        R.raf = requestAnimationFrame(() => resize(e));
    });
    document.addEventListener("mouseup", stopResize);
}



function deselectImage() {
    R.img = null;
    R.originalSrc = null;
    R.tracking = false;
    R.handles.forEach((h) => h.remove());
    R.handles = [];
}



function getOverlay() {
    let el = document.getElementById("ck-resize-overlay");
    if (!el) {
        el = document.createElement("div");
        el.id = "ck-resize-overlay";
        el.style.cssText =
            "position:fixed;left:0;top:0;width:0;height:0;z-index:999999999;pointer-events:none;";
        document.body.appendChild(el);
    }
    return el;
}



function buildHandles() {
    R.handles.forEach((h) => h.remove());
    R.handles = [];
    if (!R.img) return;

    const overlay = getOverlay();
    const cursors = [
        "nwse-resize",
        "nesw-resize",
        "nesw-resize",
        "nwse-resize",

    ];



    for (let i = 0; i < 4; i++) {

        const h = document.createElement("div");
        h.className = "custom-resize-handle";
        h.dataset.corner = i;
        h.style.cssText = `position:fixed;width:12px;height:12px;background:#0d6efd;border:2px solid #fff;border-radius:50%;z-index:999999999;cursor:${cursors[i]};pointer-events:auto;`;
        h.addEventListener("mousedown", startResize);
        overlay.appendChild(h);
        R.handles.push(h);
    }
    positionHandles();

}



function positionHandles() {

    if (!R.img || !R.img.isConnected || R.handles.length !== 4) return;
    const r = R.img.getBoundingClientRect();
    const pts = [
        [r.left, r.top],
        [r.left + r.width, r.top],
        [r.left, r.top + r.height],
        [r.left + r.width, r.top + r.height],
    ];

    pts.forEach(([x, y], i) => {
        R.handles[i].style.left = x - 6 + "px";
        R.handles[i].style.top = y - 6 + "px";
    });

}



function startTracking() {
    if (R.tracking) return;
    R.tracking = true;

    (function loop() {
        if (!R.tracking) return;
        if (!R.img || !R.img.isConnected) return deselectImage();
        positionHandles();
        requestAnimationFrame(loop);
    })();

}



function startResize(e) {
    e.preventDefault();
    e.stopPropagation();
    R.corner = parseInt(e.target.dataset.corner);
    R.resizing = true;
    R.startX = e.clientX;
    R.startW = R.img.offsetWidth;
}



function resize(e) {
    if (!R.resizing || !R.img) return;
    const diff = e.clientX - R.startX;
    const grows = R.corner === 1 || R.corner === 3;
    let w = grows ? R.startW + diff : R.startW - diff;
    R.img.style.width = Math.max(w, 100) + "px";
    R.img.style.height = "auto";
    positionHandles();
}

function stopResize() {
    R.resizing = false;
    R.corner = null;
    if (R.raf) cancelAnimationFrame(R.raf);
    R.raf = null;
    positionHandles();
    finalizeResize();
}

function finalizeResize() {

    const img = R.img;
    const originalSrc = R.originalSrc;
    const editor = window.currentEditor;
    if (!img || !originalSrc || !editor) return;
    const targetWidth = Math.round(img.getBoundingClientRect().width);
    if (targetWidth < 10) return;
    const source = new Image();
    source.onload = () => {

        if (!editor || R.img !== img) return;
        const ratio = source.naturalHeight / source.naturalWidth;
        const targetHeight = Math.round(targetWidth * ratio);
        const canvas = document.createElement("canvas");
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        canvas
            .getContext("2d")
            .drawImage(source, 0, 0, targetWidth, targetHeight);
        const newSrc = canvas.toDataURL("image/jpeg", 0.85);
        const modelImage = editor.model.document.selection.getSelectedElement();
        if (!modelImage || (modelImage.name !== "imageBlock" && modelImage.name !== "imageInline")) {
            return;
        }

        editor.model.change((writer) => {
            writer.setAttribute("src", newSrc, modelImage);
            if (modelImage.hasAttribute("width"))
                writer.removeAttribute("width", modelImage);
            if (modelImage.hasAttribute("height"))
                writer.removeAttribute("height", modelImage);
        });



        requestAnimationFrame(() => {
            const viewEl = editor.editing.mapper.toViewElement(modelImage);
            const domEl =
                viewEl && editor.editing.view.domConverter.mapViewToDom(viewEl);
            const domImg =
                domEl &&
                (domEl.tagName === "IMG" ? domEl : domEl.querySelector("img"));
            if (domImg) {
                R.img = domImg;
                R.originalSrc = originalSrc;
                R.img.style.width = "";
                R.img.style.height = "";
                R.img.style.outline = "2px solid #0d6efd";
                buildHandles();
            }
        });
    };
    source.src = originalSrc;
}

const ALIGN_ICONS = {
    left: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" class="ck ck-icon"><path d="M2 4h16v2H2V4zm0 4h10v2H2V8zm0 4h16v2H2v-2zm0 4h10v2H2v-2z" fill="currentColor"/></svg>',
    center: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" class="ck ck-icon"><path d="M2 4h16v2H2V4zm3 4h10v2H5V8zm-3 4h16v2H2v-2zm3 4h10v2H5v-2z" fill="currentColor"/></svg>',
    right: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" class="ck ck-icon"><path d="M2 4h16v2H2V4zm6 4h10v2H8V8zm-6 4h16v2H2v-2zm6 4h10v2H8v-2z" fill="currentColor"/></svg>',
};



function addAlignButtons(editor) {
    const items =
        editor.ui.view.toolbar.element.querySelector(".ck-toolbar__items") ||
        editor.ui.view.toolbar.element;
    const sep = document.createElement("span");
    sep.className = "ck-toolbar__separator";
    items.appendChild(sep);

    ["left", "center", "right"].forEach((type) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "ck ck-button ck-off";
        btn.id = `custom-img-${type}`;
        btn.title = type.charAt(0).toUpperCase() + type.slice(1);
        btn.innerHTML = `<span class="ck ck-button__icon">${ALIGN_ICONS[type]}</span>`;
        btn.onclick = () => applyAlign(type);
        items.appendChild(btn);
    });
}

function applyAlign(type) {
    const editor = window.currentEditor;
    if (!editor) return;
    const image = editor.model.document.selection.getSelectedElement();
    if (
        image &&
        (image.name === "imageBlock" || image.name === "imageInline")
    ) {
       const styleMap = {
            left: "alignLeft",
            center: "alignCenter",
            right: "alignRight",
        };
        editor.model.change((writer) =>
            writer.setAttribute("imageStyle", styleMap[type], image),
        );
        requestAnimationFrame(positionHandles);
        return;
    }
    alignText(editor, type);
}
