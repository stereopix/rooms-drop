<?php

clean_uploads();
auth_requiered();

$d = $_GET['d'] ?? false;
if (!check_drop_name($d)) {
  header("Location: ./");
  exit();
}
create_drop($d);

?><!DOCTYPE html>
<html>
  <head>
    <title>ROOMS drop</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="style.css">
    <script>
const photos_list = [];
let current_xhr = null;
let drop_name = "<?php echo $d; ?>";
function upload() {
  if (current_xhr) return;
  let i;
  for (i = 0; i < photos_list.length; i++)
    if (!photos_list[i].uploaded)
      break;
  if (i < photos_list.length) {
    const formData = new FormData()
    formData.append('file', photos_list[i].file);
    formData.append('drop', drop_name);
    formData.append('action', 'upload');

    photos_list[i].uploading = true;
    current_xhr = new XMLHttpRequest();
    current_xhr.open('POST', './?action=upload', true);
    current_xhr.upload.addEventListener("progress", e => {
      photos_list[i].bar.style.width = ((e.loaded * 100.0 / e.total) || 100) + '%';
    });
    current_xhr.addEventListener('readystatechange', e => {
      if (current_xhr.readyState == 4 && current_xhr.status == 200) {
        photos_list[i].uploaded = true;
        photos_list[i].uploading = false;
        current_xhr = null;
        upload();
      }
      else if (current_xhr.readyState == 4 && current_xhr.status != 200) {
        current_xhr = null;
        photos_list[i].uploading = false;
        photos_list[i].bar.style.width = "0%";
      }
    });
    current_xhr.send(formData);
  } else {
    const json = {
      "media": [],
      "meta": { "url_base": (new URL("./", document.location)).href }
    };
    for (let i = 0; i < photos_list.length; i++) {
      const p = { "url": "?d="+drop_name+"&f="+encodeURIComponent(photos_list[i].filename) };
      if (photos_list[i].url) p.url = photos_list[i].url;
      if (photos_list[i].swapped) p.swapped = true;
      if (photos_list[i].phantogram) p.phantogram = true;
      p.preset = photos_list[i].preset;
      p.projection = photos_list[i].projection;
      json.media.push(p);
    }
    current_xhr = new XMLHttpRequest();
    current_xhr.open('POST', './', true);
    current_xhr.addEventListener('readystatechange', e => {
      if (current_xhr.readyState == 4 && current_xhr.status == 200) {
        current_xhr = null;
      }
      else if (current_xhr.readyState == 4 && current_xhr.status != 200) {
        current_xhr = null;
      }
    });
    const formData = new FormData()
    formData.append('json', JSON.stringify(json, null, '\t'));
    formData.append('drop', drop_name);
    formData.append('action', 'upload');
    current_xhr.send(formData);
  }
}
function photoline(i, f, p, o) {
  const div_line = document.createElement("div");
  div_line.classList.add("photos_line");
  div_line.dataset.i = i;
  o.div_line = div_line;
  const spanbarout = document.createElement("span");
  spanbarout.classList.add("gauge");
  const spanbarin = document.createElement("span");
  spanbarin.style.width = p+"%";
  spanbarout.appendChild(spanbarin);
  const img = document.createElement("img");
  img.width = "300";
  const div = document.createElement("div");
  div_line.appendChild(img);
  div_line.appendChild(div);
  div.appendChild(spanbarout);
  div.appendChild(document.createElement("br"));
  if (f instanceof File) {
    const reader = new FileReader();
    reader.readAsDataURL(f);
    reader.onloadend = function() {
      img.src = reader.result;
    }
  } else {
    img.src = f;
  }
  photos.appendChild(div_line);

  const label_swapped = document.createElement("label");
  const chk_swapped = document.createElement("input");
  chk_swapped.type = "checkbox";
  if (o.swapped) chk_swapped.checked = true;
  chk_swapped.onchange = e => {
    photos_list[div_line.dataset.i].swapped = chk_swapped.checked;
  };
  label_swapped.appendChild(chk_swapped);
  label_swapped.appendChild(document.createTextNode(" Swapped"));
  div.appendChild(label_swapped);

  div.appendChild(document.createElement("br"));

  const label_phantogram = document.createElement("label");
  const chk_phantogram = document.createElement("input");
  chk_phantogram.type = "checkbox";
  if (o.phantogram) chk_phantogram.checked = true;
  chk_phantogram.onchange = e => {
    photos_list[div_line.dataset.i].phantogram = chk_phantogram.checked;
  };
  label_phantogram.appendChild(chk_phantogram);
  label_phantogram.appendChild(document.createTextNode(" Phantogram"));
  div.appendChild(label_phantogram);

  div.appendChild(document.createElement("hr"));

  const rnd = Math.random().toString(36).slice(-7);
  const preset_labels = ["Monoscopic", "Parallel side-by-side", "Squeezed parallel side-by-side", "Cross side-by-side", "Squeezed cross side-by-side", "Top-and-bottom", "Squeezed top-and-bottom"];
  const preset_values = ["mono", "sbs", "hsbs", "rl", "hrl", "tab", "htab"];
  for (let pi = 0; pi < preset_labels.length; pi++) {
    const label = document.createElement("label");
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.value = preset_values[pi];
    if (o.preset == radio.value) radio.checked = true;
    radio.name = "preset_" + rnd;
    radio.onchange = e => {
      photos_list[div_line.dataset.i].preset = radio.value;
    };
    label.appendChild(radio);
    label.appendChild(document.createTextNode(" "+preset_labels[pi]));
    div.appendChild(label);
    div.appendChild(document.createElement("br"));
  }

  div.appendChild(document.createElement("hr"));

  const projection_labels = ["Rectilinear", "VR180", "VR360"];
  const projection_values = ["rectilinear", "vr180", "vr360"];
  for (let pi = 0; pi < projection_labels.length; pi++) {
    const label = document.createElement("label");
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.value = projection_values[pi];
    if (o.projection == radio.value) radio.checked = true;
    radio.name = "projection_" + rnd;
    radio.onchange = e => {
      photos_list[div_line.dataset.i].projection = radio.value;
    };
    label.appendChild(radio);
    label.appendChild(document.createTextNode(" "+projection_labels[pi]));
    div.appendChild(label);
    div.appendChild(document.createElement("br"));
  }

  return spanbarin;
}
window.addEventListener("DOMContentLoaded", (event) => {
  const url_info = document.getElementById("url_info");
  const photos = document.getElementById("photos");
  const drop = document.getElementById("drop");

  {
    const url = new URL("./?d="+drop_name+"&f=list.json", document.location);
    const input = document.createElement("input");
    input.id = "json_url";
    input.type = "text"
    input.readOnly = true;
    input.value = url;
    url_info.appendChild(input);

    current_xhr = new XMLHttpRequest();
    current_xhr.open('GET', url, true);
    current_xhr.addEventListener('readystatechange', e => {
      if (current_xhr.readyState == 4 && current_xhr.status == 200) {
        json = JSON.parse(current_xhr.responseText);
        current_xhr = null;
        let i = 0;
        json.media.forEach(p => {
          const pli = {
            "file": null,
            "filename": p.url,
            "url": p.url,
            "bar": null,
            "preset": p.preset,
            "projection": p.projection,
            "swapped": p.swapped,
            "phantogram": p.phantogram,
            "uploading": false,
            "uploaded": true,
          };
          pli.bar = photoline(i, p.url, 100, pli);
          photos_list.push(pli);
          i += 1;
        });
        document.getElementById("btn_update").classList.add("show");
        document.getElementById("btn_update").onclick = upload;
      }
      else if (current_xhr.readyState == 4 && current_xhr.status != 200) {
        current_xhr = null;
      }
    });
    current_xhr.send('');
  }

  document.getElementById("btn_delete").onclick = () => {
    let list;
    try {
      list = JSON.parse(localStorage.getItem('drops_list'));
      if (!list) list = [];
    } catch (JSONError) {
      list = []
    }
    list = list.filter(item => item !== drop_name);
    localStorage.setItem('drops_list', JSON.stringify(list));
    document.location = "./?action=delete&d="+drop_name;
  }

  const hl = e => {
    e.preventDefault();
    e.stopPropagation();
    drop.classList.add("highlight");
  };
  const uhl = e => {
    e.preventDefault();
    e.stopPropagation();
    drop.classList.remove("highlight");
  };
  drop.addEventListener("dragenter", hl, false);
  drop.addEventListener("dragover", hl, false);
  drop.addEventListener("dragleave", uhl, false);
  drop.addEventListener("drop", e => {
    uhl(e);
    const files = e.dataTransfer.files;
    for (let i = 0; i < files.length; i++) {
      const f = files[i];
      const pli = {
        "file": f,
        "filename": f.name,
        "bar": null,
        "swapped": false,
        "phantogram": false,
        "preset": "sbs",
        "projection": "rectilinear",
        "uploading": false,
        "uploaded": false,
      };
      pli.bar = photoline(i, f, 0, pli);
      photos_list.push(pli);
    }
    document.getElementById("btn_update").classList.add("show");
    document.getElementById("btn_update").onclick = upload;
    upload();
  }, false);
});
    </script>
  </head>
  <body>
    <h1><a href="./">«</a> ROOMS drop</h1>
    <p id="url_info"></p>
    <div id="drop">DROP PHOTOS HERE (max <?php echo ini_get("upload_max_filesize").'B'; ?>)</div>
    <div id="photos"></div>
    <p><input id="btn_update" type="button" value="Update" /></p>
    <p><input id="btn_delete" type="button" value="Delete this drop" /></p>
  </body>
</html>