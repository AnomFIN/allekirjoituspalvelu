import { useNavigate, useParams } from 'react-router-dom';
import StatusBadge from '../components/StatusBadge';

const MOCK_DOCS = {
  '1': {
    id: '1',
    name: 'Työsopimus_2025.pdf',
    date: '24.4.2025',
    status: 'waiting',
    pages: 4,
    size: '245 kt',
    sentBy: 'Sinä',
    signers: [
      { name: 'Matti Virtanen', email: 'matti@esimerkki.fi', signed: true, signedAt: '24.4.2025 klo 14:32' },
      { name: 'Anna Korhonen', email: 'anna@esimerkki.fi', signed: false, signedAt: null },
      { name: 'Pekka Leinonen', email: 'pekka@esimerkki.fi', signed: false, signedAt: null },
    ],
    events: [
      { text: 'Dokumentti ladattu', time: '24.4.2025 klo 10:00', type: 'upload' },
      { text: 'Allekirjoituspyynnöt lähetetty', time: '24.4.2025 klo 10:01', type: 'sent' },
      { text: 'Matti Virtanen allekirjoitti', time: '24.4.2025 klo 14:32', type: 'signed' },
    ],
  },
  '2': {
    id: '2',
    name: 'Vuokrasopimus_Helsinki.pdf',
    date: '22.4.2025',
    status: 'signed',
    pages: 8,
    size: '512 kt',
    sentBy: 'Sinä',
    signers: [
      { name: 'Laura Mäkinen', email: 'laura@esimerkki.fi', signed: true, signedAt: '22.4.2025 klo 11:05' },
      { name: 'Jukka Hämäläinen', email: 'jukka@esimerkki.fi', signed: true, signedAt: '22.4.2025 klo 15:20' },
    ],
    events: [
      { text: 'Dokumentti ladattu', time: '22.4.2025 klo 09:00', type: 'upload' },
      { text: 'Allekirjoituspyynnöt lähetetty', time: '22.4.2025 klo 09:01', type: 'sent' },
      { text: 'Laura Mäkinen allekirjoitti', time: '22.4.2025 klo 11:05', type: 'signed' },
      { text: 'Jukka Hämäläinen allekirjoitti', time: '22.4.2025 klo 15:20', type: 'signed' },
      { text: 'Kaikki allekirjoittaneet – dokumentti valmis', time: '22.4.2025 klo 15:20', type: 'complete' },
    ],
  },
  '3': {
    id: '3',
    name: 'NDA_Sopimus_2025.pdf',
    date: '20.4.2025',
    status: 'rejected',
    pages: 2,
    size: '98 kt',
    sentBy: 'Sinä',
    signers: [
      { name: 'Sari Nieminen', email: 'sari@esimerkki.fi', signed: false, rejected: true, rejectedAt: '20.4.2025 klo 16:45', reason: 'Sopimuksen ehdot vaativat muutoksia' },
    ],
    events: [
      { text: 'Dokumentti ladattu', time: '20.4.2025 klo 14:00', type: 'upload' },
      { text: 'Allekirjoituspyyntö lähetetty', time: '20.4.2025 klo 14:01', type: 'sent' },
      { text: 'Sari Nieminen hylkäsi allekirjoituksen', time: '20.4.2025 klo 16:45', type: 'rejected' },
    ],
  },
};

const eventIcons = {
  upload: { icon: '📄', bg: 'bg-slate-100' },
  sent: { icon: '📧', bg: 'bg-blue-100' },
  signed: { icon: '✅', bg: 'bg-green-100' },
  complete: { icon: '🎉', bg: 'bg-green-100' },
  rejected: { icon: '❌', bg: 'bg-red-100' },
};

export default function DocumentDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const doc = MOCK_DOCS[id];

  if (!doc) {
    return (
      <div className="text-center py-16">
        <p className="text-slate-500">Dokumenttia ei löydy.</p>
        <button
          onClick={() => navigate('/documents')}
          className="mt-4 text-blue-700 hover:underline text-sm"
        >
          Takaisin dokumentteihin
        </button>
      </div>
    );
  }

  const signersTotal = doc.signers.length;
  const signersCompleted = doc.signers.filter((s) => s.signed).length;

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      {/* Back */}
      <button
        onClick={() => navigate(-1)}
        className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors"
      >
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
        </svg>
        Takaisin
      </button>

      {/* Header card */}
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div className="flex items-start gap-4">
            <div className="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg className="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h1 className="text-xl font-bold text-slate-900">{doc.name}</h1>
              <div className="flex flex-wrap items-center gap-3 mt-2">
                <span className="text-xs text-slate-400">{doc.date}</span>
                <span className="text-xs text-slate-400">{doc.pages} sivua</span>
                <span className="text-xs text-slate-400">{doc.size}</span>
              </div>
            </div>
          </div>
          <StatusBadge status={doc.status} />
        </div>

        {/* Progress */}
        <div className="mt-5 pt-5 border-t border-slate-100">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-slate-700">
              Allekirjoitukset: {signersCompleted}/{signersTotal}
            </span>
            <span className="text-sm text-slate-400">
              {Math.round((signersCompleted / signersTotal) * 100)}%
            </span>
          </div>
          <div className="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
            <div
              className="h-full bg-green-500 rounded-full transition-all"
              style={{ width: `${(signersCompleted / signersTotal) * 100}%` }}
            />
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Signers */}
        <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
          <h2 className="text-base font-semibold text-slate-900 mb-4">Allekirjoittajat</h2>
          <div className="space-y-3">
            {doc.signers.map((signer, i) => (
              <div key={i} className="flex items-center gap-3">
                <div className={`w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 ${
                  signer.signed ? 'bg-green-100 text-green-700' : signer.rejected ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500'
                }`}>
                  {signer.name[0].toUpperCase()}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-slate-900">{signer.name}</p>
                  <p className="text-xs text-slate-400 truncate">{signer.email}</p>
                  {signer.signed && signer.signedAt && (
                    <p className="text-xs text-green-600">{signer.signedAt}</p>
                  )}
                  {signer.rejected && (
                    <p className="text-xs text-red-500">{signer.rejectedAt} – {signer.reason}</p>
                  )}
                </div>
                <div>
                  {signer.signed ? (
                    <span className="flex items-center justify-center w-6 h-6 bg-green-100 rounded-full">
                      <svg className="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                      </svg>
                    </span>
                  ) : signer.rejected ? (
                    <span className="flex items-center justify-center w-6 h-6 bg-red-100 rounded-full">
                      <svg className="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </span>
                  ) : (
                    <span className="w-6 h-6 rounded-full border-2 border-dashed border-amber-400 block" />
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Activity */}
        <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
          <h2 className="text-base font-semibold text-slate-900 mb-4">Tapahtumahistoria</h2>
          <div className="space-y-3">
            {doc.events.map((event, i) => {
              const cfg = eventIcons[event.type] || eventIcons.upload;
              return (
                <div key={i} className="flex items-start gap-3">
                  <div className={`w-8 h-8 rounded-full ${cfg.bg} flex items-center justify-center text-sm flex-shrink-0`}>
                    {cfg.icon}
                  </div>
                  <div>
                    <p className="text-sm text-slate-800">{event.text}</p>
                    <p className="text-xs text-slate-400">{event.time}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Actions */}
      {doc.status === 'waiting' && (
        <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-wrap gap-3">
          <button className="flex items-center gap-2 bg-blue-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-800 transition-colors">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Lähetä muistutus
          </button>
          <button className="flex items-center gap-2 border border-slate-200 text-slate-600 px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Lataa kopio
          </button>
          <button className="flex items-center gap-2 border border-red-200 text-red-600 px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors ml-auto">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            Peruuta pyyntö
          </button>
        </div>
      )}

      {doc.status === 'signed' && (
        <div className="bg-green-50 border border-green-200 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <div className="flex items-center gap-3 flex-1">
            <svg className="w-8 h-8 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p className="font-semibold text-green-800">Dokumentti allekirjoitettu!</p>
              <p className="text-sm text-green-600">Kaikki osapuolet ovat allekirjoittaneet dokumentin.</p>
            </div>
          </div>
          <button className="flex items-center gap-2 bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-green-800 transition-colors whitespace-nowrap">
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Lataa allekirjoitettu PDF
          </button>
        </div>
      )}
    </div>
  );
}
